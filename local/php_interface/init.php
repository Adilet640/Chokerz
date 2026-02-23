<?php
/**
 * init.php — инициализация проекта CHOKERZ
 *
 * Выполняется Битрикс автоматически на каждый запрос до загрузки всего остального.
 *
 * Изменения (2026-02-23):
 *  - Удалён вызов CBitrixComponent::SetCacheSettings() — метода не существует
 *  - Удалено дублирующее подключение JS/CSS (подключаются в header.php через Asset)
 *  - Добавлена константа CHOKERZ_DEBUG для управления режимом отладки
 *
 * Путь: local/php_interface/init.php
 *
 * @package CHOKERZ
 * @version 1.1
 */

// Режим отладки — определяется на уровне конфигурации сервера (не хардкодим)
if (!defined('CHOKERZ_DEBUG')) {
    define('CHOKERZ_DEBUG', false);
}

if (!CHOKERZ_DEBUG) {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Подключение обработчиков событий
$eventsFile = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/events.php';
if (file_exists($eventsFile)) {
    include_once $eventsFile;
}

// Подключение вспомогательных функций
$functionsFile = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/functions.php';
if (file_exists($functionsFile)) {
    include_once $functionsFile;
}

// Примечание: JS и CSS подключаются в header.php шаблона через Asset::getInstance().
// Дублировать их здесь нельзя — двойная загрузка каждого ресурса на странице.
