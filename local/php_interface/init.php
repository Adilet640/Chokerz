<?php
/**
 * Инициализация кастомных настроек проекта CHOKERZ
 */

// Отключаем вывод ошибок на продакшене
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', false);
}

if (!DEBUG_MODE) {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Подключение кастомных обработчиков событий
if (file_exists($_SERVER['DOCUMENT_ROOT'].'/local/php_interface/include/events.php')) {
    include_once($_SERVER['DOCUMENT_ROOT'].'/local/php_interface/include/events.php');
}

// Подключение кастомных функций
if (file_exists($_SERVER['DOCUMENT_ROOT'].'/local/php_interface/include/functions.php')) {
    include_once($_SERVER['DOCUMENT_ROOT'].'/local/php_interface/include/functions.php');
}

// Настройки кэширования
\CBitrixComponent::SetCacheSettings(array(
    'ttl' => 3600, // 1 час
    'cache_type' => 'managed',
));

// Отключаем стандартные стили Битрикс
\Bitrix\Main\Page\Asset::getInstance()->addJs('/local/templates/chokerz/js/main.js');
\Bitrix\Main\Page\Asset::getInstance()->addCss('/local/templates/chokerz/styles/main.css');
