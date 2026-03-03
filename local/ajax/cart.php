<?php
/**
 * AJAX-обработчик корзины — Bitrix D7
 *
 * @package CHOKERZ
 * @version 2.0
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Fuser;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

/**
 * Отправить JSON-ответ и завершить выполнение
 */
function sendJson(array $data): void
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    die();
}

/**
 * Собрать items[] + count + total из объекта корзины
 */
function buildBasketPayload(Basket $basket): array
{
    $items = [];
    /** @var BasketItem $item */
    foreach ($basket->getBasketItems() as $item) {
        $items[] = [
            'id'       => $item->getId(),
            'productId'=> $item->getProductId(),
            'name'     => $item->getField('NAME'),
            'price'    => $item->getPrice(),
            'quantity' => $item->getQuantity(),
            'sum'      => $item->getFinalPrice(),
        ];
    }

    return [
        'items' => $items,
        'count' => array_sum(array_column($items, 'quantity')),
        'total' => $basket->getPrice(),
    ];
}

// ── Загрузка модуля ──────────────────────────────────────────────────────────

if (!Loader::includeModule('sale')) {
    sendJson(['success' => false, 'message' => 'Модуль sale недоступен']);
}

// ── Разбор запроса ───────────────────────────────────────────────────────────

$request  = Application::getInstance()->getContext()->getRequest();
$action   = (string)$request->getPost('action');
$sessid   = (string)$request->getPost('sessid');

// CSRF-проверка (все write-действия)
$writeActions = ['add', 'remove', 'update', 'clear'];
if (in_array($action, $writeActions, true) && !check_bitrix_sessid($sessid)) {
    sendJson(['success' => false, 'message' => 'Неверный sessid']);
}

$productId  = (int)$request->getPost('productId');
$basketId   = (int)$request->getPost('basketId');   // ID строки корзины для remove/update
$quantity   = max(1, (int)$request->getPost('quantity'));

// ── Получение корзины ────────────────────────────────────────────────────────

$fuserId = (int)Fuser::getId(true); // D7: создаёт fuser для гостя если не существует

$basket = Basket::loadItemsForFUser($fuserId, SITE_ID);

// ── Действия ─────────────────────────────────────────────────────────────────

switch ($action) {

    // ── GET ──────────────────────────────────────────────────────────────────
    case 'get':
        $payload = buildBasketPayload($basket);
        sendJson(array_merge(['success' => true], $payload));
        break;

    // ── ADD ──────────────────────────────────────────────────────────────────
    case 'add':
        if ($productId <= 0) {
            sendJson(['success' => false, 'message' => 'Не передан productId']);
        }

        // Если товар уже есть — увеличиваем количество
        $existingItem = null;
        /** @var BasketItem $bItem */
        foreach ($basket->getBasketItems() as $bItem) {
            if ($bItem->getProductId() === $productId) {
                $existingItem = $bItem;
                break;
            }
        }

        if ($existingItem !== null) {
            $result = $existingItem->setField('QUANTITY', $existingItem->getQuantity() + $quantity);
        } else {
            $item   = $basket->createItem('catalog', $productId);
            $result = $item->setFields([
                'QUANTITY'             => $quantity,
                'CURRENCY'             => \Bitrix\Currency\CurrencyManager::getBaseCurrency(),
                'LID'                  => SITE_ID,
                'PRODUCT_PROVIDER_CLASS' => \Bitrix\Catalog\Product\CatalogProvider::class,
            ]);
        }

        if ($result->isSuccess()) {
            $saveResult = $basket->save();
            if ($saveResult->isSuccess()) {
                $payload = buildBasketPayload($basket);
                sendJson(array_merge(['success' => true, 'message' => 'Товар добавлен в корзину'], $payload));
            }
            sendJson(['success' => false, 'message' => implode('; ', $saveResult->getErrorMessages())]);
        }
        sendJson(['success' => false, 'message' => implode('; ', $result->getErrorMessages())]);
        break;

    // ── REMOVE ───────────────────────────────────────────────────────────────
    case 'remove':
        if ($basketId <= 0) {
            sendJson(['success' => false, 'message' => 'Не передан basketId']);
        }

        $found = false;
        /** @var BasketItem $bItem */
        foreach ($basket->getBasketItems() as $bItem) {
            if ($bItem->getId() === $basketId) {
                $result = $bItem->delete();
                if (!$result->isSuccess()) {
                    sendJson(['success' => false, 'message' => implode('; ', $result->getErrorMessages())]);
                }
                $found = true;
                break;
            }
        }

        if (!$found) {
            sendJson(['success' => false, 'message' => 'Позиция корзины не найдена']);
        }

        $saveResult = $basket->save();
        if ($saveResult->isSuccess()) {
            $payload = buildBasketPayload($basket);
            sendJson(array_merge(['success' => true, 'message' => 'Товар удалён из корзины'], $payload));
        }
        sendJson(['success' => false, 'message' => implode('; ', $saveResult->getErrorMessages())]);
        break;

    // ── UPDATE ───────────────────────────────────────────────────────────────
    case 'update':
        if ($basketId <= 0) {
            sendJson(['success' => false, 'message' => 'Не передан basketId']);
        }

        $found = false;
        foreach ($basket->getBasketItems() as $bItem) {
            if ($bItem->getId() === $basketId) {
                $result = $bItem->setField('QUANTITY', $quantity);
                if (!$result->isSuccess()) {
                    sendJson(['success' => false, 'message' => implode('; ', $result->getErrorMessages())]);
                }
                $found = true;
                break;
            }
        }

        if (!$found) {
            sendJson(['success' => false, 'message' => 'Позиция корзины не найдена']);
        }

        $saveResult = $basket->save();
        if ($saveResult->isSuccess()) {
            $payload = buildBasketPayload($basket);
            sendJson(array_merge(['success' => true, 'message' => 'Количество обновлено'], $payload));
        }
        sendJson(['success' => false, 'message' => implode('; ', $saveResult->getErrorMessages())]);
        break;

    // ── CLEAR ────────────────────────────────────────────────────────────────
    case 'clear':
        $result = $basket->clearCollection();
        if ($result->isSuccess()) {
            $saveResult = $basket->save();
            if ($saveResult->isSuccess()) {
                sendJson(['success' => true, 'message' => 'Корзина очищена', 'items' => [], 'count' => 0, 'total' => 0]);
            }
            sendJson(['success' => false, 'message' => implode('; ', $saveResult->getErrorMessages())]);
        }
        sendJson(['success' => false, 'message' => implode('; ', $result->getErrorMessages())]);
        break;

    // ── UNKNOWN ──────────────────────────────────────────────────────────────
    default:
        sendJson(['success' => false, 'message' => 'Неизвестное действие: ' . htmlspecialchars($action)]);
}
