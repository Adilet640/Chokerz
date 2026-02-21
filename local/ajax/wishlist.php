<?php
/**
 * AJAX обработчик для избранного
 * 
 * @author VibePilot
 * @version 1.0
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\Query;

// Установка заголовков для JSON ответа и запрет кэширования (для отладки)
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Получение данных из запроса (проверка на валидность)
$request = Application::getInstance()->getContext()->getRequest();
$action = $request->getPost('action');
$productId = (int)$request->getPost('productId');
$userId = (int)$request->getPost('userId');

// Проверка авторизации пользователя (для безопасности)
global $USER;
if (!$USER->IsAuthorized()) {
    echo json_encode([
        'success' => false,
        'message' => 'Пользователь не авторизован'
    ]);
    die();
}

// Проверка наличия модуля highloadblock
if (!Loader::includeModule('highloadblock')) {
    echo json_encode([
        'success' => false,
        'message' => 'Не удалось подключить модуль высоконагруженных блоков'
    ]);
    die();
}

// Получение таблицы избранного (заглушка, т.к. таблица еще не создана)
$hlBlockId = 1; // ID HL-блока "Избранное" (нужно настроить в админке)
$hlBlock = HighloadBlockTable::getById($hlBlockId)->fetch();

if (!$hlBlock) {
    echo json_encode([
        'success' => false,
        'message' => 'HL-блок не найден'
    ]);
    die();
}

// Получение объекта сущности HL-блока (заглушка)
$entityDataClass = HighloadBlockTable::compileEntity($hlBlock)->getDataClass();

// Обработка действий (добавление/удаление)
if ($action === 'add') {
    // Проверка наличия товара в избранном пользователя (заглушка)
    $existing = $entityDataClass::getList([
        'filter' => [
            'UF_USER_ID' => $userId,
            'UF_PRODUCT_ID' => $productId
        ]
    ])->fetch();

    if ($existing) {
        echo json_encode([
            'success' => false,
            'message' => 'Товар уже добавлен в избранное'
        ]);
        die();
    }

    // Добавление товара в избранное (заглушка)
    $result = $entityDataClass::add([
        'UF_USER_ID' => $userId,
        'UF_PRODUCT_ID' => $productId,
        'UF_DATE_ADD' => new \Bitrix\Main\Type\DateTime()
    ]);

    if ($result->isSuccess()) {
        echo json_encode([
            'success' => true,
            'message' => 'Товар добавлен в избранное',
            'id' => $result->getId()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка при добавлении в избранное: ' . implode(', ', $result->getErrorMessages())
        ]);
    }

} elseif ($action === 'remove') {
    // Удаление товара из избранного (заглушка)
    $existing = $entityDataClass::getList([
        'filter' => [
            'UF_USER_ID' => $userId,
            'UF_PRODUCT_ID' => $productId
        ]
    ])->fetch();

    if (!$existing) {
        echo json_encode([
            'success' => false,
            'message' => 'Товар не найден в избранном'
        ]);
        die();
    }

    $result = $entityDataClass::delete($existing['ID']);

    if ($result->isSuccess()) {
        echo json_encode([
            'success' => true,
            'message' => 'Товар удалён из избранного'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка при удалении из избранного: ' . implode(', ', $result->getErrorMessages())
        ]);
    }

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Неизвестное действие'
    ]);
}

die();
