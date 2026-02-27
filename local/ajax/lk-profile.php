<?php
/**
 * AJAX-обработчик действий в ЛК профиля CHOKERZ
 * URL: /local/ajax/lk-profile.php
 *
 * Поддерживаемые действия (POST-параметр "action"):
 *   - save_address    — сохранить адрес доставки (CSaleOrderUserProps)
 *   - delete_address  — удалить адрес (id)
 *   - tg_disconnect   — отвязать Telegram (сброс UF_TG_CHAT_ID)
 */

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
require_once $DOCUMENT_ROOT . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;

/**
 * Отправляет JSON-ответ и завершает выполнение
 */
function lkJsonResponse(bool $success, string $message = '', array $data = []): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data,
    ], JSON_UNESCAPED_UNICODE);
    die();
}

// Только POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lkJsonResponse(false, 'Method not allowed');
}

// Проверка сессии
if (!check_bitrix_sessid()) {
    lkJsonResponse(false, 'CSRF-токен недействителен');
}

// Только авторизованные
global $USER;
if (!$USER->IsAuthorized()) {
    lkJsonResponse(false, 'Требуется авторизация');
}

$userId = (int)$USER->GetID();
$action = trim($_POST['action'] ?? '');

// ================================================================
// ДЕЙСТВИЕ: Сохранить адрес
// ================================================================
if ($action === 'save_address') {
    if (!Loader::includeModule('sale')) {
        lkJsonResponse(false, 'Модуль sale не подключён');
    }

    $profileName = trim($_POST['profile_name'] ?? '');
    $addressFull = trim($_POST['address_full'] ?? '');

    if ($profileName === '' || $addressFull === '') {
        lkJsonResponse(false, 'Название профиля и адрес обязательны');
    }

    // Ограничение: максимум 10 адресов на пользователя
    $existingCount = CSaleOrderUserProps::GetList([], ['USER_ID' => $userId], [], ['nTopCount' => 1])->SelectedRowsCount();
    if ($existingCount >= 10) {
        lkJsonResponse(false, 'Максимум 10 адресов. Удалите неиспользуемые.');
    }

    $newId = CSaleOrderUserProps::Add([
        'USER_ID' => $userId,
        'NAME'    => htmlspecialcharsEx($profileName),
        'XML_ID'  => '',
    ]);

    if (!$newId) {
        lkJsonResponse(false, 'Ошибка сохранения адреса');
    }

    lkJsonResponse(true, 'Адрес сохранён', ['id' => (int)$newId, 'name' => $profileName]);
}

// ================================================================
// ДЕЙСТВИЕ: Удалить адрес
// ================================================================
if ($action === 'delete_address') {
    if (!Loader::includeModule('sale')) {
        lkJsonResponse(false, 'Модуль sale не подключён');
    }

    $addressId = (int)($_POST['id'] ?? 0);
    if ($addressId <= 0) {
        lkJsonResponse(false, 'Некорректный ID адреса');
    }

    // Проверка принадлежности адреса текущему пользователю
    $row = CSaleOrderUserProps::GetList(
        [],
        ['ID' => $addressId, 'USER_ID' => $userId],
        false,
        ['nTopCount' => 1]
    )->Fetch();

    if (!$row) {
        lkJsonResponse(false, 'Адрес не найден');
    }

    $result = CSaleOrderUserProps::Delete($addressId);
    if (!$result) {
        lkJsonResponse(false, 'Ошибка удаления адреса');
    }

    lkJsonResponse(true, 'Адрес удалён');
}

// ================================================================
// ДЕЙСТВИЕ: Отвязать Telegram
// ================================================================
if ($action === 'tg_disconnect') {
    $result = UserTable::update($userId, ['UF_TG_CHAT_ID' => null]);

    if ($result->isSuccess()) {
        lkJsonResponse(true, 'Telegram отключён');
    }

    lkJsonResponse(false, implode(', ', $result->getErrorMessages()));
}

lkJsonResponse(false, 'Неизвестное действие');
