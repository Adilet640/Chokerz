<?php
/**
 * Страница каталога товаров (для создания через компонент "Создание страницы" в админке)
 * 
 * @author VibePilot
 * @version 1.0
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<?php
// Подключение компонентов страницы каталога товаров (через $APPLICATION->IncludeComponent)
$APPLICATION->IncludeComponent(
    'bitrix:catalog',
    '.default',
    [
        'IBLOCK_ID' => 1, // ID инфоблока каталога товаров (настроить в админке)
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/catalog/',
        'SEF_URL_TEMPLATES' => [
            'sections' => '',
            'section' => '#SECTION_CODE#/',
            'element' => '#SECTION_CODE#/#ELEMENT_CODE#/',
            'compare' => 'compare.php?action=#ACTION_CODE#',
        ],
        'VARIABLE_ALIASES' => [
            'sections' => [],
            'section' => [],
            'element' => [],
        ],
        'CACHE_TYPE' => 'A',
        'CACHE_TIME' => '36000',
        'CACHE_GROUPS' => 'Y',
        'SET_TITLE' => 'Y',
        'ADD_SECTIONS_CHAIN' => 'Y',
        'ADD_ELEMENT_CHAIN' => 'Y',
        'SHOW_404' => 'N',
        'FILE_404' => '',
    ]
);
?>
