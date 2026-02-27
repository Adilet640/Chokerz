<?php
/**
 * Компонент списка блога CHOKERZ
 * Выводит статьи и видео из инфоблока «Блог»
 * Фильтрация по типу (Статья / Видео), категории, тегу
 * Пагинация: desktop — классическая + «Загрузить ещё», mobile — только «Загрузить ещё»
 *
 * @package chokerz
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Application;

class BlogListComponent extends CBitrixComponent
{
    /** Допустимые типы материалов */
    private const TYPES = ['article', 'video'];

    public function onPrepareComponentParams($arParams): array
    {
        $arParams['IBLOCK_TYPE']   = $arParams['IBLOCK_TYPE']   ?? 'blog';
        $arParams['IBLOCK_ID']     = (int)($arParams['IBLOCK_ID'] ?? 0);
        $arParams['PAGE_SIZE']     = (int)($arParams['PAGE_SIZE'] ?? 9);
        $arParams['CACHE_TIME']    = (int)($arParams['CACHE_TIME'] ?? 3600);
        $arParams['DETAIL_URL']    = $arParams['DETAIL_URL'] ?? '/blog/#CODE#/';

        return $arParams;
    }

    public function executeComponent(): void
    {
        if (!Loader::includeModule('iblock')) {
            ShowError('Модуль iblock не подключён');
            return;
        }

        // Параметры фильтрации из GET
        $type     = $this->sanitizeType($_GET['type'] ?? 'all');
        $section  = (int)($_GET['section'] ?? 0);
        $tag      = trim($_GET['tag'] ?? '');
        $page     = max(1, (int)($_GET['page'] ?? 1));

        $this->arResult['FILTER'] = [
            'TYPE'    => $type,
            'SECTION' => $section,
            'TAG'     => $tag,
            'PAGE'    => $page,
        ];

        // AJAX-запрос «Загрузить ещё» — отдаём только карточки
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
            && !empty($_GET['ajax_load']);

        $cacheId  = 'blog_list_' . md5(serialize([$type, $section, $tag, $page, $this->arParams['PAGE_SIZE']]));
        $cacheDir = '/blog/list/' . $this->arParams['IBLOCK_ID'];
        $cache    = Cache::createInstance();

        if ($cache->initCache($this->arParams['CACHE_TIME'], $cacheId, $cacheDir)) {
            $this->arResult = array_merge($this->arResult, $cache->getVars());
        } elseif ($cache->startDataCache()) {
            $tagCache = Application::getInstance()->getTaggedCache();
            $tagCache->startTagCache($cacheDir);
            $tagCache->registerTag('iblock_id_' . $this->arParams['IBLOCK_ID']);

            $data = $this->fetchItems($type, $section, $tag, $page);
            $this->arResult = array_merge($this->arResult, $data);

            $tagCache->endTagCache();
            $cache->endDataCache($data);
        }

        // Секции (категории) для фильтра
        $this->arResult['SECTIONS'] = $this->fetchSections();

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'items'      => $this->arResult['ITEMS'],
                'pagination' => $this->arResult['PAGINATION'],
            ], JSON_UNESCAPED_UNICODE);
            die();
        }

        $this->IncludeComponentTemplate();
    }

    /**
     * Выборка материалов блога
     */
    private function fetchItems(string $type, int $section, string $tag, int $page): array
    {
        $pageSize = $this->arParams['PAGE_SIZE'];
        $offset   = ($page - 1) * $pageSize;

        // Фильтр
        $filter = [
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'ACTIVE'    => 'Y',
        ];

        if ($type !== 'all') {
            $filter['PROPERTY_TYPE'] = $type === 'video' ? 'video' : 'article';
        }

        if ($section > 0) {
            $filter['SECTION_ID'] = $section;
        }

        if ($tag !== '') {
            $filter['TAGS'] = $tag;
        }

        // Общее количество (для пагинации)
        $totalCount = (int)\CIBlockElement::GetList(
            [],
            $filter,
            [],
            false,
            ['ID']
        )->SelectedRowsCount();

        // Сброс — SelectedRowsCount не работает с paginaton, используем COUNT
        $dbCount = \CIBlockElement::GetList([], $filter, []);
        $totalCount = (int)$dbCount->SelectedRowsCount();

        // Выборка с пагинацией
        $res = \CIBlockElement::GetList(
            ['ACTIVE_FROM' => 'DESC'],
            $filter,
            false,
            ['nPageSize' => $pageSize, 'iNumPage' => $page, 'checkOutOfRange' => true],
            ['ID', 'NAME', 'CODE', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'PREVIEW_TEXT', 'ACTIVE_FROM', 'TAGS', 'SECTION_ID']
        );

        $items = [];
        while ($el = $res->GetNextElement()) {
            $fields = $el->GetFields();
            $props  = $el->GetProperties(['CODE' => ['TYPE', 'VIDEO_ID', 'VIDEO_DURATION']]);

            $previewSrc = '';
            if ($fields['PREVIEW_PICTURE']) {
                $file = \CFile::GetFileArray($fields['PREVIEW_PICTURE']);
                $previewSrc = $file ? \CFile::GetFileSRC($file) : '';
            }

            $items[] = [
                'ID'             => $fields['ID'],
                'NAME'           => $fields['NAME'],
                'CODE'           => $fields['CODE'],
                'DETAIL_URL'     => $fields['DETAIL_PAGE_URL'],
                'PREVIEW_SRC'    => $previewSrc,
                'PREVIEW_TEXT'   => $fields['PREVIEW_TEXT'],
                'DATE'           => $fields['ACTIVE_FROM'] ? date('d.m.Y', MakeTimeStamp($fields['ACTIVE_FROM'])) : '',
                'TAGS'           => array_filter(array_map('trim', explode(',', $fields['TAGS'] ?? ''))),
                'SECTION_ID'     => $fields['SECTION_ID'],
                'TYPE'           => $props['TYPE']['VALUE'] ?? 'article',
                'VIDEO_ID'       => $props['VIDEO_ID']['VALUE'] ?? '',
                'VIDEO_DURATION' => $props['VIDEO_DURATION']['VALUE'] ?? '',
                'IS_VIDEO'       => ($props['TYPE']['VALUE'] ?? '') === 'video',
            ];
        }

        $totalPages = $pageSize > 0 ? (int)ceil($totalCount / $pageSize) : 1;

        return [
            'ITEMS'      => $items,
            'PAGINATION' => [
                'CURRENT'     => $page,
                'TOTAL_PAGES' => $totalPages,
                'TOTAL_ITEMS' => $totalCount,
                'HAS_NEXT'    => $page < $totalPages,
                'NEXT_PAGE'   => $page + 1,
            ],
        ];
    }

    /**
     * Секции (категории) инфоблока блога для фильтра
     */
    private function fetchSections(): array
    {
        $result = [];
        $res = \CIBlockSection::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => $this->arParams['IBLOCK_ID'], 'ACTIVE' => 'Y'],
            false,
            ['ID', 'NAME', 'CODE']
        );
        while ($row = $res->Fetch()) {
            $result[] = $row;
        }
        return $result;
    }

    /**
     * Валидация типа материала
     */
    private function sanitizeType(string $type): string
    {
        return in_array($type, [...self::TYPES, 'all'], true) ? $type : 'all';
    }
}
