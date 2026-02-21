<?php
/**
 * Компонент карточки товара (кастомный)
 * 
 * @author VibePilot
 * @version 1.0
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;
use Bitrix\Catalog\ProductTable;

/**
 * Class CatalogItemComponent
 */
class CatalogItemComponent extends CBitrixComponent
{
    /**
     * Выполнение компонента
     */
    public function executeComponent()
    {
        // Загрузка модулей
        if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog')) {
            ShowError('Не удалось подключить модули инфоблоков или каталога');
            return;
        }

        // Проверка наличия элемента
        $this->arResult['ELEMENT_ID'] = (int)$this->arParams['ELEMENT_ID'];
        
        if ($this->arResult['ELEMENT_ID'] <= 0) {
            ShowError('Элемент не найден');
            return;
        }

        // Получение данных элемента
        $this->arResult['ELEMENT'] = $this->getElementData($this->arResult['ELEMENT_ID']);
        
        if (!$this->arResult['ELEMENT']) {
            ShowError('Элемент не найден');
            return;
        }

        // Получение торговых предложений (SKU)
        $this->arResult['OFFERS'] = $this->getOffers($this->arResult['ELEMENT_ID']);

        // Проверка кэширования
        if ($this->StartResultCache(false, [], '/'.$this->arResult['ELEMENT']['ID'])) {
            $this->includeComponentTemplate();
        }
    }

    /**
     * Получение данных элемента
     * 
     * @param int $elementId ID элемента
     * @return array|null Данные элемента или null если не найден
     */
    private function getElementData($elementId)
    {
        $result = ElementTable::getList([
            'select' => [
                'ID',
                'NAME',
                'DETAIL_PAGE_URL',
                'PREVIEW_PICTURE',
                'DETAIL_PICTURE',
                'PREVIEW_TEXT',
                'DETAIL_TEXT',
                'IBLOCK_SECTION_ID',
                'CATALOG_QUANTITY',
                'CATALOG_PRICE_1',
                'CATALOG_CURRENCY_1',
                'PROPERTY_TYPE',
                'PROPERTY_MATERIAL',
                'PROPERTY_SIZE',
                'PROPERTY_COLOR',
                'PROPERTY_ARTICLE',
                'PROPERTY_HIT',
                'PROPERTY_NEW',
                'PROPERTY_SALE',
                'PROPERTY_OZON_LINK',
                'PROPERTY_WB_LINK',
                'PROPERTY_YM_LINK'
            ],
            'filter' => [
                '=ID' => $elementId,
                '=ACTIVE' => 'Y'
            ]
        ])->fetch();

        if (!$result) {
            return null;
        }

        // Обработка изображений
        if ($result['PREVIEW_PICTURE']) {
            $result['PREVIEW_PICTURE_FILE'] = CFile::GetPath($result['PREVIEW_PICTURE']);
        }
        
        if ($result['DETAIL_PICTURE']) {
            $result['DETAIL_PICTURE_FILE'] = CFile::GetPath($result['DETAIL_PICTURE']);
        }

        // Форматирование цены
        if ($result['CATALOG_PRICE_1']) {
            $result['FORMATTED_PRICE'] = number_format(
                $result['CATALOG_PRICE_1'], 
                0, 
                '.', 
                ' '
            ) . ' ' . $result['CATALOG_CURRENCY_1'];
        }

        return $result;
    }

    /**
     * Получение торговых предложений (SKU)
     * 
     * @param int $elementId ID элемента
     * @return array Массив торговых предложений
     */
    private function getOffers($elementId)
    {
        // Здесь будет логика получения SKU
        // Для простоты возвращаем пустой массив
        return [];
    }
}
