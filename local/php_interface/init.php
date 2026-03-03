<?php
/**
 * init.php — инициализация проекта CHOKERZ
 *
 * Выполняется Битрикс автоматически на каждый запрос до загрузки всего остального.
 *
 * Изменения (2026-03-03):
 *  - Регистрация агента перенесена на паттерн "однократная проверка через Option":
 *    вместо CAgent::GetList() (SQL на каждый хит) используется флаг-опция
 *    chokerz / ozon_agent_registered. Запрос к БД происходит только один раз
 *    за весь срок жизни установки — при отсутствии флага.
 *  - CAgent допускается как осознанное исключение: полноценного D7-аналога
 *    для управления агентами в Битрикс не существует (аналогично Fuser::getId()).
 *    Факт задокументирован комментарием.
 *
 * Изменения (2026-02-23):
 *  - Удалён вызов CBitrixComponent::SetCacheSettings() — метода не существует
 *  - Удалено дублирующее подключение JS/CSS (подключаются в header.php через Asset)
 *  - Добавлена константа CHOKERZ_DEBUG для управления режимом отладки
 *
 * Путь: local/php_interface/init.php
 *
 * @package CHOKERZ
 * @version 1.2
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

// ─── Регистрация агента синхронизации OZON-отзывов ────────────────────────
//
// Паттерн: однократная проверка через Option-флаг.
//
// Проблема наивной реализации:
//   CAgent::GetList() в init.php выполняет SQL на каждый web-хит.
//   При высокой нагрузке это выливается в тысячи лишних запросов к БД в сутки.
//
// Решение:
//   После первой успешной регистрации агента ставим флаг в таблицу опций
//   (b_option) через Bitrix\Main\Config\Option::set().
//   На последующих запросах Option::get() возвращает значение из кеша
//   менеджера опций — без обращения к БД.
//
// Осознанное отклонение:
//   CAgent используется как единственный публичный API регистрации агентов
//   в Битрикс D7 (аналог Fuser::getId()). D7-замена на момент написания
//   отсутствует. Использование задокументировано.
//
if (
    defined('SITE_ID')          // исключаем CLI-контекст cron-скрипта
    && class_exists('CAgent')   // модуль main инициализирован (всегда после prolog)
) {
    $agentName   = '\\ChokerzCronHandlers::syncOzonReviews();';
    $agentPeriod = 86400; // 1 сутки
    $optionModule = 'chokerz';
    $optionKey    = 'ozon_agent_registered';

    // Читаем флаг — Option кеширует значение, SQL выполняется лишь при первом чтении
    $isRegistered = \Bitrix\Main\Config\Option::get($optionModule, $optionKey, '');

    if ($isRegistered !== 'Y') {
        // CAgent::Add — осознанное исключение, D7-аналога нет
        $agentId = \CAgent::Add([
            'MODULE_ID'      => $optionModule,
            'AGENT_INTERVAL' => $agentPeriod,
            'ACTIVE'         => 'Y',
            'NAME'           => $agentName,
            // Первый запуск — через 5 минут после регистрации
            'NEXT_EXEC'      => \ConvertTimeStamp(time() + 300, 'FULL'),
        ]);

        if ($agentId) {
            // Ставим флаг — теперь этот блок кода будет пропускаться на всех следующих хитах
            \Bitrix\Main\Config\Option::set($optionModule, $optionKey, 'Y');
        }
    }
}

// Примечание: JS и CSS подключаются в header.php шаблона через Asset::getInstance().
// Дублировать их здесь нельзя — двойная загрузка каждого ресурса на странице.
