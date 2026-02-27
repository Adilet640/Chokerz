<?php
/**
 * Компонент поиска CHOKERZ
 * Единое поле поиска. Результаты: вкладки «Все» / «Товары» / «Информация»
 * Товары — через CIBlockElement + фильтр/сортировка.
 * Информация (блог, страницы) — через стандартный CSearch.
 * Исправление опечаток — стандартный модуль поиска Битрикс (stemmer).
 *
 * @package chokerz
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;

class SearchResultsComponent extends CBitrixComponent
{
    /** Допустимые вкладки */
    private const TABS = ['all', 'products', 'info'];

    /** Допустимые параметры сортировки товаров */
    private const SORT_FIELDS = ['date', 'popularity', 'price_asc', 'price_desc', 'rating'];

    public function onPrepareComponentParams($arParams): array
    {
        $arParams['CATALOG_IBLOCK_ID']  = (int)($arParams['CATALOG_IBLOCK_ID']  ?? 0);
        $arParams['BLOG_IBLOCK_ID']     = (int)($arParams['BLOG_IBLOCK_ID']     ?? 0);
        $arParams['PAGES_IBLOCK_ID']    = (int)($arParams['PAGES_IBLOCK_ID']    ?? 0);
        $arParams['PRODUCTS_PAGE_SIZE'] = (int)($arParams['PRODUCTS_PAGE_SIZE'] ?? 12);
        $arParams['INFO_PAGE_SIZE']     = (int)($arParams['INFO_PAGE_SIZE']      ?? 6);
        $arParams['CACHE_TIME']         = (int)($arParams['CACHE_TIME']          ?? 300);

        return $arParams;
    }

    public function executeComponent(): void
    {
        if (!Loader::includeModule('iblock') || !Loader::includeModule('search')) {
            ShowError('Модули iblock / search не подключены');
            return;
        }

        // Входные параметры запроса
        $query   = trim($_GET['q'] ?? '');
        $tab     = $this->sanitizeTab($_GET['tab'] ?? 'all');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $sort    = $this->sanitizeSort($_GET['sort'] ?? 'date');

        // Фильтры каталога из GET (передаются фильтром каталога)
        $priceMin  = (int)($_GET['price_min'] ?? 0);
        $priceMax  = (int)($_GET['price_max'] ?? 0);
        $filterProps = $this->extractFilterProps();

        $this->arResult = [
            'QUERY'      => $query,
            'TAB'        => $tab,
            'SORT'       => $sort,
            'PAGE'       => $page,
            'PRODUCTS'   => [],
            'INFO'       => [],
            'TOTAL'      => ['PRODUCTS' => 0, 'INFO' => 0],
            'PAGINATION' => [],
            'SUGGEST'    => '', // исправленный запрос от стандартного поиска
        ];

        if ($query === '') {
            $this->IncludeComponentTemplate();
            return;
        }

        // Кеш зависит от всех параметров запроса
        $cacheId  = 'search_' . md5($query . $tab . $page . $sort . $priceMin . $priceMax . serialize($filterProps));
        $cacheDir = '/search/results';
        $cache    = Cache::createInstance();

        if ($cache->initCache($this->arParams['CACHE_TIME'], $cacheId, $cacheDir)) {
            $cached = $cache->getVars();
            $this->arResult = array_merge($this->arResult, $cached);
        } elseif ($cache->startDataCache()) {
            $data = $this->performSearch($query, $tab, $page, $sort, $priceMin, $priceMax, $filterProps);
            $this->arResult = array_merge($this->arResult, $data);
            $cache->endDataCache($data);
        }

        $this->IncludeComponentTemplate();
    }

    /**
     * Основной поиск
     */
    private function performSearch(
        string $query,
        string $tab,
        int    $page,
        string $sort,
        int    $priceMin,
        int    $priceMax,
        array  $filterProps
    ): array {

        $result = [
            'PRODUCTS'    => [],
            'INFO'        => [],
            'TOTAL'       => ['PRODUCTS' => 0, 'INFO' => 0],
            'PAGINATION'  => ['PRODUCTS' => [], 'INFO' => []],
            'SUGGEST'     => '',
            'FILTER_PROPS'=> [],
        ];

        // Товары нужны на вкладках «Все» и «Товары»
        if (in_array($tab, ['all', 'products'], true)) {
            $products = $this->searchProducts($query, $page, $sort, $priceMin, $priceMax, $filterProps);
            $result['PRODUCTS']             = $products['ITEMS'];
            $result['TOTAL']['PRODUCTS']    = $products['TOTAL'];
            $result['PAGINATION']['PRODUCTS'] = $products['PAGINATION'];
            $result['FILTER_PROPS']         = $products['FILTER_PROPS'];
        }

        // Информация нужна на вкладках «Все» и «Информация»
        if (in_array($tab, ['all', 'info'], true)) {
            $info = $this->searchInfo($query, $page);
            $result['INFO']                 = $info['ITEMS'];
            $result['TOTAL']['INFO']        = $info['TOTAL'];
            $result['PAGINATION']['INFO']   = $info['PAGINATION'];
        }

        // Suggest (исправление опечаток) через стандартный модуль
        $result['SUGGEST'] = $this->getSuggest($query);

        return $result;
    }

    /**
     * Поиск товаров через CIBlockElement
     * Использует стандартный full-text поиск (SEARCHABLE_CONTENT)
     */
    private function searchProducts(
        string $query,
        int    $page,
        string $sort,
        int    $priceMin,
        int    $priceMax,
        array  $filterProps
    ): array {

        $pageSize = $this->arParams['PRODUCTS_PAGE_SIZE'];

        [$sortField, $sortOrder] = $this->parseSortParams($sort);

        // Базовый фильтр
        $filter = [
            'IBLOCK_ID'           => $this->arParams['CATALOG_IBLOCK_ID'],
            'ACTIVE'              => 'Y',
            'SEARCHABLE_CONTENT'  => '%' . $query . '%',
        ];

        // Фильтр цены
        if ($priceMin > 0) {
            $filter['>=CATALOG_PRICE_1'] = $priceMin;
        }
        if ($priceMax > 0) {
            $filter['<=CATALOG_PRICE_1'] = $priceMax;
        }

        // Фильтр по свойствам (Цвет, Материал, Размер и т.д.)
        foreach ($filterProps as $code => $values) {
            if (!empty($values)) {
                $filter['PROPERTY_' . $code] = $values;
            }
        }

        // Сортировка
        $sortArray = [$sortField => $sortOrder];
        if ($sort === 'popularity') {
            $sortArray = ['PROPERTY_POPULARITY' => 'DESC'];
        } elseif ($sort === 'rating') {
            $sortArray = ['PROPERTY_RATING' => 'DESC'];
        }

        // Общее кол-во
        $countRes = \CIBlockElement::GetList($sortArray, $filter, []);
        $total    = (int)$countRes->SelectedRowsCount();

        // Страница
        $res = \CIBlockElement::GetList(
            $sortArray,
            $filter,
            false,
            ['nPageSize' => $pageSize, 'iNumPage' => $page],
            [
                'ID', 'NAME', 'CODE', 'DETAIL_PAGE_URL',
                'PREVIEW_PICTURE', 'PREVIEW_TEXT',
                'CATALOG_PRICE_1', 'CATALOG_CURRENCY_1',
                'PROPERTY_ARTICLE', 'PROPERTY_HIT', 'PROPERTY_NEW',
                'PROPERTY_COLOR', 'PROPERTY_MATERIAL', 'PROPERTY_SIZE',
            ]
        );

        $items = [];
        while ($el = $res->GetNextElement()) {
            $fields = $el->GetFields();
            $props  = $el->GetProperties();

            $previewSrc = '';
            if ($fields['PREVIEW_PICTURE']) {
                $file = \CFile::GetFileArray($fields['PREVIEW_PICTURE']);
                $previewSrc = $file ? \CFile::GetFileSRC($file) : '';
            }

            $items[] = [
                'ID'          => $fields['ID'],
                'NAME'        => $fields['NAME'],
                'CODE'        => $fields['CODE'],
                'DETAIL_URL'  => $fields['DETAIL_PAGE_URL'],
                'PREVIEW_SRC' => $previewSrc,
                'PRICE'       => $fields['CATALOG_PRICE_1'] ?? 0,
                'CURRENCY'    => $fields['CATALOG_CURRENCY_1'] ?? 'RUB',
                'ARTICLE'     => $props['ARTICLE']['VALUE'] ?? '',
                'IS_HIT'      => !empty($props['HIT']['VALUE']),
                'IS_NEW'      => !empty($props['NEW']['VALUE']),
                'COLOR'       => $props['COLOR']['VALUE'] ?? '',
                'MATERIAL'    => $props['MATERIAL']['VALUE'] ?? '',
            ];
        }

        // Фильтр-пропсы для отображения в сайдбаре
        $filterPropsList = $this->getFilterProps($filter);

        $totalPages = $pageSize > 0 ? (int)ceil($total / $pageSize) : 1;

        return [
            'ITEMS'        => $items,
            'TOTAL'        => $total,
            'FILTER_PROPS' => $filterPropsList,
            'PAGINATION'   => [
                'CURRENT'     => $page,
                'TOTAL_PAGES' => $totalPages,
                'TOTAL_ITEMS' => $total,
                'HAS_NEXT'    => $page < $totalPages,
                'NEXT_PAGE'   => $page + 1,
            ],
        ];
    }

    /**
     * Поиск информационных материалов через стандартный CSearch
     * Охватывает: блог (статьи, видео) + страницы
     */
    private function searchInfo(string $query, int $page): array
    {
        $pageSize = $this->arParams['INFO_PAGE_SIZE'];
        $offset   = ($page - 1) * $pageSize;

        $obSearch = new CSearch();
        $obSearch->SetOptions(['STEMMING' => true]);

        // Поиск только по инфоблокам блога и страниц
        $iblockIds = array_filter([
            $this->arParams['BLOG_IBLOCK_ID'],
            $this->arParams['PAGES_IBLOCK_ID'],
        ]);

        $obSearch->Search(
            [
                'QUERY'    => $query,
                'SITE_ID'  => SITE_ID,
                'MODULE'   => 'iblock',
                'PARAM1'   => $iblockIds ?: null,
            ],
            ['RANK' => 'DESC'],
            []
        );

        $total = (int)$obSearch->GetTotalCount();
        $obSearch->NavStart($pageSize, false, $page);

        $items = [];
        while ($row = $obSearch->GetNext()) {
            // Тип материала по PARAM1 (IBLOCK_ID)
            $iblockId = (int)($row['PARAM1'] ?? 0);
            $type     = 'page';
            if ($iblockId === $this->arParams['BLOG_IBLOCK_ID']) {
                $type = 'blog';
            }

            $items[] = [
                'ID'         => $row['ITEM_ID']   ?? '',
                'TITLE'      => $row['TITLE']     ?? '',
                'URL'        => $row['URL']        ?? '',
                'BODY'       => $this->truncateText($row['BODY'] ?? '', 200),
                'DATE'       => $row['DATE_CHANGE'] ?? '',
                'TYPE'       => $type,
                'IBLOCK_ID'  => $iblockId,
            ];
        }

        $totalPages = $pageSize > 0 ? (int)ceil($total / $pageSize) : 1;

        return [
            'ITEMS'      => $items,
            'TOTAL'      => $total,
            'PAGINATION' => [
                'CURRENT'     => $page,
                'TOTAL_PAGES' => $totalPages,
                'TOTAL_ITEMS' => $total,
                'HAS_NEXT'    => $page < $totalPages,
                'NEXT_PAGE'   => $page + 1,
            ],
        ];
    }

    /**
     * Suggest — исправление запроса через стандартный поиск Битрикс
     */
    private function getSuggest(string $query): string
    {
        try {
            $obSearch = new CSearch();
            $obSearch->SetOptions(['STEMMING' => true]);
            $obSearch->Search(['QUERY' => $query, 'SITE_ID' => SITE_ID], [], []);

            // GetCorrectedQuery доступен в расширенном API, иначе пустая строка
            $corrected = method_exists($obSearch, 'GetCorrectedQuery')
                ? (string)$obSearch->GetCorrectedQuery()
                : '';

            return ($corrected !== $query) ? $corrected : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Возвращает уникальные значения свойств для сайдбарного фильтра
     */
    private function getFilterProps(array $baseFilter): array
    {
        $props = [];
        $filterForProps = $baseFilter;

        // Убираем фильтры которые уже применены для честного подсчёта
        unset(
            $filterForProps['PROPERTY_COLOR'],
            $filterForProps['PROPERTY_MATERIAL'],
            $filterForProps['PROPERTY_SIZE'],
            $filterForProps['>=CATALOG_PRICE_1'],
            $filterForProps['<=CATALOG_PRICE_1']
        );

        foreach (['COLOR' => 'Цвет', 'MATERIAL' => 'Материал', 'SIZE' => 'Размер'] as $code => $name) {
            $values = [];
            $res = \CIBlockElement::GetList(
                [],
                $filterForProps,
                false,
                false,
                ['PROPERTY_' . $code]
            );
            while ($row = $res->GetNextElement()) {
                $p = $row->GetProperties(['CODE' => [$code]]);
                $val = $p[$code]['VALUE'] ?? '';
                if ($val !== '' && !in_array($val, $values, true)) {
                    $values[] = $val;
                }
            }

            if (!empty($values)) {
                $props[$code] = ['NAME' => $name, 'VALUES' => $values];
            }
        }

        return $props;
    }

    /**
     * Разбор параметра сортировки в поле + направление для CIBlockElement
     */
    private function parseSortParams(string $sort): array
    {
        return match ($sort) {
            'price_asc'  => ['CATALOG_PRICE_1', 'ASC'],
            'price_desc' => ['CATALOG_PRICE_1', 'DESC'],
            default      => ['ACTIVE_FROM', 'DESC'],
        };
    }

    /**
     * Извлечение свойств фильтра из GET
     */
    private function extractFilterProps(): array
    {
        $allowed = ['COLOR', 'MATERIAL', 'SIZE', 'TYPE_PRODUCT'];
        $result  = [];

        foreach ($allowed as $code) {
            $key = 'filter_' . strtolower($code);
            if (!empty($_GET[$key])) {
                $raw = $_GET[$key];
                $result[$code] = is_array($raw)
                    ? array_map('trim', $raw)
                    : [trim($raw)];
            }
        }

        return $result;
    }

    /**
     * Обрезка текста с сохранением слова
     */
    private function truncateText(string $text, int $len): string
    {
        $text = strip_tags($text);
        if (mb_strlen($text) <= $len) {
            return $text;
        }
        $truncated = mb_substr($text, 0, $len);
        $lastSpace = mb_strrpos($truncated, ' ');
        return ($lastSpace !== false ? mb_substr($truncated, 0, $lastSpace) : $truncated) . '…';
    }

    private function sanitizeTab(string $tab): string
    {
        return in_array($tab, self::TABS, true) ? $tab : 'all';
    }

    private function sanitizeSort(string $sort): string
    {
        return in_array($sort, self::SORT_FIELDS, true) ? $sort : 'date';
    }
}
