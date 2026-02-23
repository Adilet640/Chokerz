if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;
use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\ProductTable;

class CatalogItemComponent extends CBitrixComponent
{
    /** @var int ID инфоблока каталога, берётся из параметров компонента */
    private int $iblockId = 0;

    /**
     * Точка входа компонента.
     * Порядок: проверка параметров → StartResultCache → запросы → EndResultCache → шаблон.
     */
    public function executeComponent(): void
    {
        if (!$this->loadModules()) {
            return;
        }

        $this->iblockId = (int)($this->arParams['IBLOCK_ID'] ?? 0);
        $elementId      = (int)($this->arParams['ELEMENT_ID'] ?? 0);
        $cacheTime      = (int)($this->arParams['CACHE_TIME'] ?? 3600);

        if ($elementId <= 0) {
            ShowError('Не передан ID элемента');
            return;
        }

        // -----------------------------------------------------------------------
        // Кэш: StartResultCache вызывается ДО запросов к БД.
        // Ключ кэша строится из ID элемента, чтобы при изменении товара
        // управляемый кэш автоматически сбрасывался через тег iblock_id_N.
        // -----------------------------------------------------------------------
        if ($this->StartResultCache($cacheTime, $elementId)) {

            $element = $this->getElementData($elementId);

            if (!$element) {
                $this->AbortResultCache();
                ShowError('Элемент с ID=' . $elementId . ' не найден или не активен');
                return;
            }

            $this->arResult['ELEMENT'] = $element;
            $this->arResult['OFFERS']  = $this->getOffers($elementId);

            $this->SetResultCacheKeys(['ELEMENT', 'OFFERS']);

            $this->EndResultCache();
        }

        $this->includeComponentTemplate();
    }

    /**
     * Загрузка обязательных модулей Битрикс.
     */
    private function loadModules(): bool
    {
        if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog')) {
            ShowError('Не удалось подключить модули iblock / catalog');
            return false;
        }
        return true;
    }

    /**
     * Получение данных элемента каталога через D7 API.
     *
     * Поля элемента  — через ElementTable.
     * Цена           — через PriceTable (тип цены 1 = базовая).
     * Остаток        — через ProductTable.
     * Свойства       — через CIBlockElement::GetProperty.
     *
     * @param int $elementId
     * @return array|null
     */
    private function getElementData(int $elementId): ?array
    {
        // 1. Основные поля элемента
        $row = ElementTable::getList([
            'select' => [
                'ID',
                'NAME',
                'CODE',
                'DETAIL_PAGE_URL',
                'PREVIEW_PICTURE',
                'DETAIL_PICTURE',
                'PREVIEW_TEXT',
                'DETAIL_TEXT',
                'IBLOCK_SECTION_ID',
                'IBLOCK_ID',
            ],
            'filter' => [
                '=ID'     => $elementId,
                '=ACTIVE' => 'Y',
            ],
        ])->fetch();

        if (!$row) {
            return null;
        }

        // 2. Изображения
        if ($row['PREVIEW_PICTURE']) {
            $row['PREVIEW_PICTURE_SRC'] = \CFile::GetPath($row['PREVIEW_PICTURE']);
        }
        if ($row['DETAIL_PICTURE']) {
            $row['DETAIL_PICTURE_SRC'] = \CFile::GetPath($row['DETAIL_PICTURE']);
        }

        // 3. Цена через PriceTable
        $priceTypeId = (int)($this->arParams['PRICE_TYPE_ID'] ?? 1);

        $priceRow = PriceTable::getList([
            'select' => ['PRICE', 'CURRENCY'],
            'filter' => [
                '=PRODUCT_ID'       => $elementId,
                '=CATALOG_GROUP_ID' => $priceTypeId,
            ],
            'limit' => 1,
        ])->fetch();

        if ($priceRow) {
            $row['PRICE']           = $priceRow['PRICE'];
            $row['CURRENCY']        = $priceRow['CURRENCY'];
            $row['FORMATTED_PRICE'] = number_format((float)$priceRow['PRICE'], 0, '.', ' ')
                . ' ' . $priceRow['CURRENCY'];
        }

        // 4. Остаток через ProductTable
        $productRow = ProductTable::getList([
            'select' => ['QUANTITY', 'AVAILABLE'],
            'filter' => ['=ID' => $elementId],
            'limit'  => 1,
        ])->fetch();

        if ($productRow) {
            $row['CATALOG_QUANTITY']  = (float)$productRow['QUANTITY'];
            $row['CATALOG_AVAILABLE'] = $productRow['AVAILABLE'];
        } else {
            $row['CATALOG_QUANTITY']  = 0;
            $row['CATALOG_AVAILABLE'] = 'N';
        }

        // 5. Свойства через CIBlockElement::GetProperty
        $propertyCodes = [
            'TYPE',
            'MATERIAL',
            'PURPOSE',
            'SIZE',
            'COLOR',
            'ARTICLE',
            'HIT',
            'NEW',
            'SALE',
            'OZON_LINK',
            'WB_LINK',
            'YM_LINK',
        ];

        $rsProps = \CIBlockElement::GetProperty(
            $row['IBLOCK_ID'],
            $elementId,
            ['sort' => 'asc'],
            ['CODE' => $propertyCodes]
        );

        while ($prop = $rsProps->Fetch()) {
            $code = $prop['CODE'];

            if ($prop['PROPERTY_TYPE'] === 'L') {
                $row['PROPERTIES'][$code]['VALUE']        = $prop['VALUE'];
                $row['PROPERTIES'][$code]['VALUE_XML_ID'] = $prop['VALUE_XML_ID'];
            } elseif ($prop['PROPERTY_TYPE'] === 'F') {
                $row['PROPERTIES'][$code]['VALUE'] = $prop['VALUE']
                    ? \CFile::GetPath($prop['VALUE'])
                    : null;
            } else {
                $row['PROPERTIES'][$code]['VALUE'] = $prop['VALUE'];
            }
        }

        return $row;
    }

    /**
     * Получение торговых предложений (SKU).
     *
     * @param int $elementId ID родительского элемента
     * @return array
     */
    private function getOffers(int $elementId): array
    {
        $offersIblockId = (int)($this->arParams['OFFERS_IBLOCK_ID'] ?? 0);

        if ($offersIblockId <= 0) {
            return [];
        }

        $offers = [];

        $rsOffers = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            [
                'IBLOCK_ID'          => $offersIblockId,
                'PROPERTY_CML2_LINK' => $elementId,
                'ACTIVE'             => 'Y',
                'CATALOG_AVAILABLE'  => 'Y',
            ],
            false,
            false,
            ['ID', 'NAME', 'PROPERTY_SIZE', 'PROPERTY_COLOR', 'CATALOG_PRICE_1', 'CATALOG_QUANTITY']
        );

        while ($offer = $rsOffers->GetNextElement()) {
            $offerFields = $offer->GetFields();
            $offerProps  = $offer->GetProperties();

            $offers[] = [
                'ID'        => $offerFields['ID'],
                'NAME'      => $offerFields['NAME'],
                'SIZE'      => $offerProps['SIZE']['VALUE']          ?? null,
                'COLOR'     => $offerProps['COLOR']['VALUE']         ?? null,
                'COLOR_HEX' => $offerProps['COLOR']['VALUE_XML_ID']  ?? null,
                'PRICE'     => $offerFields['CATALOG_PRICE_1']       ?? null,
                'QUANTITY'  => $offerFields['CATALOG_QUANTITY']      ?? 0,
            ];
        }

        return $offers;
    }
}



