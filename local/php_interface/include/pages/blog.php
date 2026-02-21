<?php
/**
 * Страница блога (для создания через компонент "Создание страницы" в админке)
 * 
 * @author VibePilot
 * @version 1.0
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<?php
// Подключение компонентов страницы блога (через $APPLICATION->IncludeComponent)
$APPLICATION->IncludeComponent(
    'bitrix:news',
    '.default',
    [
        'IBLOCK_ID' => 2, // ID инфоблока блога (настроить в админке)
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/blog/',
        'SEF_URL_TEMPLATES' => [
            'news' => '',
            'section' => '#SECTION_CODE#/',
            'detail' => '#ELEMENT_CODE#/',
        ],
        'VARIABLE_ALIASES' => [
            'news' => [],
            'section' => [],
            'detail' => [],
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
