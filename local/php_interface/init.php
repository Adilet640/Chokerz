 * @package CHOKERZ
 * @version 1.1
 */

// Режим отладки: определяется на уровне конфигурации сервера,
// не хардкодим значение здесь
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

// Примечание: JS и CSS подключаются в header.php шаблона через Asset::getInstance(),
// чтобы они добавлялись только когда шаблон реально используется.
// Дублировать их здесь нельзя — Asset::addJs() при повторе добавляет тег дважды
// в тех версиях Битрикс, где нет дедупликации по умолчанию.
