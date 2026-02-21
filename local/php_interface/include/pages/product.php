<?php
/**
 * Страница детального просмотра товара (для создания через компонент "Создание страницы" в админке)
 * 
 * @author VibePilot
 * @version 1.0
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<?php
// Подключение компонентов страницы детального просмотра товара (через $APPLICATION->IncludeComponent)
$APPLICATION->IncludeComponent(
    'bitrix:catalog.element',
    '.default',
    [
        'IBLOCK_ID' => 1, // ID инфоблока каталога товаров (настроить в админке)
        'ELEMENT_ID' => $_REQUEST['ELEMENT_ID'],
        'ELEMENT_CODE' => $_REQUEST['ELEMENT_CODE'],
        'FIELD_CODE' => [
            'ID',
            'NAME',
            'PREVIEW_PICTURE',
            'DETAIL_PICTURE',
            'PREVIEW_TEXT',
            'DETAIL_TEXT',
            'CATALOG_PRICE_1',
            'CATALOG_CURRENCY_1',
        ],
        'PROPERTY_CODE' => [
            'TYPE',
            'MATERIAL',
            'SIZE',
            'COLOR',
            'ARTICLE',
            'HIT',
            'NEW',
            'SALE',
            'OZON_LINK',
            'WB_LINK',
            'YM_LINK',
        ],
        'CACHE_TYPE' => 'A',
        'CACHE_TIME' => '36000',
        'CACHE_GROUPS' => 'Y',
        'SET_TITLE' => 'Y',
        'ADD_SECTIONS_CHAIN' => 'Y',
        'ADD_ELEMENT_CHAIN' => 'Y',
        'SHOW_404' => 'Y',
        'FILE_404' => '',
    ]
);
?>
