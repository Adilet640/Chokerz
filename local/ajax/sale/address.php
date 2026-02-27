<?php
/**
 * AJAX: /local/ajax/sale/address.php
 * Сохранение / обновление адреса доставки пользователя
 * Использует: CSaleLocation + пользовательские поля через стандартный модуль sale
 * Принимает: POST ACTION(SAVE_ADDRESS), ADDRESS_ID(optional), CITY, STREET, HOUSE, BUILDING, FLAT, ZIP, IS_DEFAULT
 * Возвращает: JSON {success, address_id?, errors?}
 */

declare(strict_types=1);

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

$siteRoot = $_SERVER['DOCUMENT_ROOT'];
require_once $siteRoot . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Delivery\Services\Manager as DeliveryManager;

header('Content-Type: application/json; charset=utf-8');

// --- Защита ---
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
    || !check_bitrix_sessid()
) {
    http_response_code(403);
    echo Json::encode(['success' => false, 'errors' => ['Forbidden']]);
    die();
}

$request = Application::getInstance()->getContext()->getRequest();
if (!$request->isAjaxRequest()) {
    http_response_code(400);
    echo Json::encode(['success' => false, 'errors' => ['AJAX only']]);
    die();
}

global $USER;

if (!$USER->IsAuthorized()) {
    http_response_code(401);
    echo Json::encode(['success' => false, 'errors' => ['Необходима авторизация']]);
    die();
}

Loader::includeModule('sale');

// --- Сбор данных ---
$action    = (string)($request->getPost('ACTION') ?? '');
$addressId = (int)($request->getPost('ADDRESS_ID') ?? 0);
$city      = trim((string)($request->getPost('CITY') ?? ''));
$street    = trim((string)($request->getPost('STREET') ?? ''));
$house     = trim((string)($request->getPost('HOUSE') ?? ''));
$building  = trim((string)($request->getPost('BUILDING') ?? ''));
$flat      = trim((string)($request->getPost('FLAT') ?? ''));
$zip       = trim((string)($request->getPost('ZIP') ?? ''));
$isDefault = ($request->getPost('IS_DEFAULT') === 'Y') ? 'Y' : 'N';

// --- Серверная валидация ---
$fieldErrors = [];

if (empty($city)) {
    $fieldErrors['CITY'] = 'Укажите город';
}
if (empty($street)) {
    $fieldErrors['STREET'] = 'Укажите улицу';
}
if (empty($house)) {
    $fieldErrors['HOUSE'] = 'Укажите номер дома';
}
if (!empty($zip) && !preg_match('/^\d{6}$/', $zip)) {
    $fieldErrors['ZIP'] = 'Индекс должен состоять из 6 цифр';
}

if (!empty($fieldErrors)) {
    echo Json::encode(['success' => false, 'field_errors' => $fieldErrors]);
    die();
}

// Формируем строку адреса для хранения в поле заказа
$addressParts = array_filter([$city, 'ул. ' . $street, 'д. ' . $house]);
if (!empty($building)) {
    $addressParts[] = 'корп. ' . $building;
}
if (!empty($flat)) {
    $addressParts[] = 'кв. ' . $flat;
}
$addressString = implode(', ', $addressParts);
if (!empty($zip)) {
    $addressString = $zip . ', ' . $addressString;
}

$userId = (int)$USER->GetID();

/**
 * Адреса доставки хранятся через Highload-блок "UserAddresses"
 * Структура HL-блока (создаётся в админке):
 *   UF_USER_ID     — Integer, привязка к пользователю
 *   UF_NAME        — String, название адреса (необязательно)
 *   UF_ADDRESS     — String, полный адрес
 *   UF_CITY        — String
 *   UF_STREET      — String
 *   UF_HOUSE       — String
 *   UF_BUILDING    — String
 *   UF_FLAT        — String
 *   UF_ZIP         — String
 *   UF_IS_DEFAULT  — Boolean
 *   UF_ACTIVE      — Boolean
 */
$hlBlockId = (int)(defined('CHOKERZ_HL_USER_ADDRESSES_ID') ? CHOKERZ_HL_USER_ADDRESSES_ID : 0);

if ($hlBlockId <= 0) {
    echo Json::encode(['success' => false, 'errors' => ['Highload-блок адресов не настроен. Установите константу CHOKERZ_HL_USER_ADDRESSES_ID в /local/php_interface/init.php']]);
    die();
}

$hlDataClass = null;
try {
    $hlBlock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlBlockId)->fetch();
    if (!$hlBlock) {
        throw new \RuntimeException('HL-блок не найден: ID=' . $hlBlockId);
    }
    $hlDataClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlBlock)->getDataClass();
} catch (\Throwable $e) {
    echo Json::encode(['success' => false, 'errors' => [$e->getMessage()]]);
    die();
}

// Если выбран «по умолчанию» — сбрасываем флаг у остальных адресов пользователя
if ($isDefault === 'Y') {
    $existingAddresses = $hlDataClass::getList([
        'filter' => ['UF_USER_ID' => $userId, 'UF_IS_DEFAULT' => true],
        'select' => ['ID'],
    ]);
    while ($addr = $existingAddresses->fetch()) {
        $hlDataClass::update($addr['ID'], ['UF_IS_DEFAULT' => false]);
    }
}

$fields = [
    'UF_USER_ID'   => $userId,
    'UF_ADDRESS'   => $addressString,
    'UF_CITY'      => $city,
    'UF_STREET'    => $street,
    'UF_HOUSE'     => $house,
    'UF_BUILDING'  => $building,
    'UF_FLAT'      => $flat,
    'UF_ZIP'       => $zip,
    'UF_IS_DEFAULT' => ($isDefault === 'Y'),
    'UF_ACTIVE'    => true,
];

if ($addressId > 0) {
    // Обновление: проверяем принадлежность адреса текущему пользователю
    $existing = $hlDataClass::getById($addressId)->fetch();
    if (!$existing || (int)$existing['UF_USER_ID'] !== $userId) {
        http_response_code(403);
        echo Json::encode(['success' => false, 'errors' => ['Доступ запрещён']]);
        die();
    }
    $result = $hlDataClass::update($addressId, $fields);
    $savedId = $addressId;
} else {
    $result  = $hlDataClass::add($fields);
    $savedId = $result->getId();
}

if ($result->isSuccess()) {
    echo Json::encode(['success' => true, 'address_id' => $savedId, 'address_string' => $addressString]);
} else {
    echo Json::encode(['success' => false, 'errors' => $result->getErrorMessages()]);
}

die();
