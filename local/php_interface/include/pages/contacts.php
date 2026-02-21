<?php
/**
 * Страница контактов (для создания через компонент "Создание страницы" в админке)
 * 
 * @author VibePilot
 * @version 1.0
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<?php
// Подключение компонентов страницы контактов (через $APPLICATION->IncludeComponent)
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '.default',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH' => '/contacts.php', // Путь к файлу контактов (настроить в админке)
        'AREA_FILE_RECURSIVE' => 'N',
        'EDIT_TEMPLATE' => '',
        'CACHE_TYPE' => 'A',
        'CACHE_TIME' => '3600',
        'CACHE_GROUPS' => 'Y',
    ]
);
?>
