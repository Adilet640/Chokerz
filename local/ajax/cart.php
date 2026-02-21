<?php
/**
 * AJAX обработчик для корзины (заглушка для будущей реализации)
 * 
 * @author VibePilot
 * @version 1.0
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Sale\Basket;

// Установка заголовков для JSON ответа и запрет кэширования (для отладки)
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Получение данных из запроса (проверка на валидность)
$request = Application::getInstance()->getContext()->getRequest();
$action = $request->getPost('action');
$productId = (int)$request->getPost('productId');
$quantity = (int)$request->getPost('quantity', 1);

// Проверка авторизации пользователя (для безопасности)
global $USER;
if (!$USER->IsAuthorized()) {
    echo json_encode([
        'success' => false,
        'message' => 'Пользователь не авторизован'
    ]);
    die();
}

// Проверка наличия модуля sale
if (!Loader::includeModule('sale')) {
    echo json_encode([
        'success' => false,
        'message' => 'Не удалось подключить модуль продаж'
    ]);
    die();
}

// Получение корзины пользователя (заглушка)
$fuserId = \CSaleBasket::GetBasketUserID(true);

// Обработка действий (добавление/удаление/обновление)
if ($action === 'add') {
    // Добавление товара в корзину (заглушка)
    $result = \CSaleBasket::Add([
        'PRODUCT_ID' => $productId,
        'QUANTITY' => $quantity,
        'FUSER_ID' => $fuserId,
        'LID' => SITE_ID,
        'CURRENCY' => 'RUB'
    ]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Товар добавлен в корзину',
            'basketId' => $result
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка при добавлении в корзину'
        ]);
    }

} elseif ($action === 'remove') {
    // Удаление товара из корзины (заглушка)
    $basketItem = Basket::getList([
        'filter' => [
            'FUSER_ID' => $fuserId,
            'PRODUCT_ID' => $productId
        ]
    ])->fetch();

    if ($basketItem) {
        $result = Basket::delete($basketItem['ID']);

        if ($result->isSuccess()) {
            echo json_encode([
                'success' => true,
                'message' => 'Товар удалён из корзины'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Ошибка при удалении из корзины: ' . implode(', ', $result->getErrorMessages())
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Товар не найден в корзине'
        ]);
    }

} elseif ($action === 'update') {
    // Обновление количества товара в корзине (заглушка)
    $basketItem = Basket::getList([
        'filter' => [
            'FUSER_ID' => $fuserId,
            'PRODUCT_ID' => $productId
        ]
    ])->fetch();

    if ($basketItem) {
        $result = Basket::update($basketItem['ID'], [
            'QUANTITY' => $quantity
        ]);

        if ($result->isSuccess()) {
            echo json_encode([
                'success' => true,
                'message' => 'Количество товара обновлено'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Ошибка при обновлении количества: ' . implode(', ', $result->getErrorMessages())
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Товар не найден в корзине'
        ]);
    }

} elseif ($action === 'get') {
    // Получение данных корзины (заглушка)
    $basketItems = Basket::getList([
        'filter' => ['FUSER_ID' => $fuserId],
        'select' => ['ID', 'PRODUCT_ID', 'QUANTITY', 'NAME', 'PRICE']
    ])->fetchAll();

    echo json_encode([
        'success' => true,
        'items' => $basketItems,
        'count' => count($basketItems)
    ]);

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Неизвестное действие'
    ]);
}

die();
