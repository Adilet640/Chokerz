<?php
/**
 * Обработчик события OnSaleStatusOrder — уведомление в Telegram при смене статуса заказа
 * Подключается в: /local/php_interface/init.php
 *
 * Добавить в init.php:
 * require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/tg_order_notify.php';
 */

if (!defined('B_PROLOG_INCLUDED') && !defined('STOP_STATISTICS')) {
    // Файл подключается из init.php, не выполняется напрямую
}

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\Config\Option;

/**
 * Отправляет уведомление пользователю в Telegram при смене статуса заказа
 *
 * @param string $orderId   ID заказа
 * @param string $newStatus Новый STATUS_ID
 */
function chokerz_tg_notify_order_status(string $orderId, string $newStatus): void
{
    if (!Loader::includeModule('sale')) {
        return;
    }

    $botToken = Option::get('chokerz', 'tg_bot_token', '');
    if ($botToken === '') {
        return;
    }

    // Получаем заказ
    $order = \Bitrix\Sale\Order::load((int)$orderId);
    if (!$order) {
        return;
    }

    $userId = (int)$order->getUserId();
    if ($userId <= 0) {
        return;
    }

    // Получаем chat_id пользователя
    $userRow = UserTable::getList([
        'filter' => ['=ID' => $userId],
        'select' => ['UF_TG_CHAT_ID', 'NAME'],
        'limit'  => 1,
    ])->fetch();

    $chatId = (int)($userRow['UF_TG_CHAT_ID'] ?? 0);
    if ($chatId === 0) {
        return;
    }

    // Словарь статусов (расширяется под реальные статусы проекта)
    $statusLabels = [
        'N' => 'Принят в обработку',
        'P' => 'Оплачен',
        'A' => 'Комплектуется',
        'D' => 'Передан в доставку',
        'F' => 'Выполнен',
        'C' => 'Отменён',
    ];

    $statusLabel = $statusLabels[$newStatus] ?? $newStatus;
    $orderNum    = htmlspecialcharsEx($order->getField('ACCOUNT_NUMBER'));
    $userName    = htmlspecialcharsEx($userRow['NAME'] ?? '');

    $text = "📦 CHOKERZ — Обновление заказа\n\n";
    $text .= "Привет, {$userName}!\n";
    $text .= "Заказ №{$orderNum}: {$statusLabel}\n\n";
    $text .= "Подробнее: https://chokerz.ru/personal/order/detail/{$orderId}/";

    chokerz_tg_send($chatId, $text, $botToken);
}

/**
 * Отправка сообщения Telegram Bot API
 */
function chokerz_tg_send(int $chatId, string $text, string $botToken): void
{
    $url     = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $payload = json_encode([
        'chat_id'    => $chatId,
        'text'       => $text,
        'parse_mode' => 'HTML',
    ], JSON_UNESCAPED_UNICODE);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n",
            'content' => $payload,
            'timeout' => 5,
        ],
    ]);

    @file_get_contents($url, false, $ctx);
}

// ================================================================
// Регистрация обработчика события
// ================================================================
AddEventHandler('sale', 'OnSaleStatusOrder', static function (string $orderId, string $newStatus): void {
    chokerz_tg_notify_order_status($orderId, $newStatus);
});
