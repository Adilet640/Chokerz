<?php
/**
 * Webhook Telegram-бота CHOKERZ
 * URL: /local/ajax/tg-webhook.php
 *
 * Регистрация webhook:
 * https://api.telegram.org/bot{TOKEN}/setWebhook?url=https://chokerz.ru/local/ajax/tg-webhook.php
 *
 * Алгоритм верификации:
 * 1. Пользователь переходит по deeplink /start TOKEN
 * 2. Бот получает команду, извлекает TOKEN
 * 3. Webhook ищет пользователя по UF_TG_TOKEN + проверяет UF_TG_TOKEN_EXPIRE
 * 4. При успехе — сохраняет chat_id в UF_TG_CHAT_ID, сбрасывает токен
 */

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
require_once $DOCUMENT_ROOT . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Config\Option;

// Проверка секретного заголовка Telegram
$botToken       = Option::get('chokerz', 'tg_bot_token', '');
$secretHeader   = Option::get('chokerz', 'tg_webhook_secret', '');

if ($secretHeader !== '') {
    $incomingSecret = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
    if (!hash_equals($secretHeader, $incomingSecret)) {
        http_response_code(403);
        die('Forbidden');
    }
}

// Читаем тело запроса
$rawBody = file_get_contents('php://input');
if (empty($rawBody)) {
    http_response_code(200);
    die();
}

$update = json_decode($rawBody, true);
if (!is_array($update)) {
    http_response_code(200);
    die();
}

// Обрабатываем только команду /start TOKEN
$message = $update['message'] ?? null;
if (!$message) {
    http_response_code(200);
    die();
}

$text    = trim($message['text'] ?? '');
$chatId  = (int)($message['chat']['id'] ?? 0);

if (!str_starts_with($text, '/start ') || $chatId === 0) {
    tgSendMessage($chatId, 'Привет! Используйте ссылку из личного кабинета CHOKERZ для подключения.', $botToken);
    http_response_code(200);
    die();
}

$token = trim(substr($text, 7));

if (strlen($token) !== 32 || !ctype_xdigit($token)) {
    tgSendMessage($chatId, 'Ссылка недействительна. Получите новую в личном кабинете.', $botToken);
    http_response_code(200);
    die();
}

// Ищем пользователя по токену
$userRow = UserTable::getList([
    'filter' => ['=UF_TG_TOKEN' => $token],
    'select' => ['ID', 'NAME', 'UF_TG_TOKEN_EXPIRE', 'UF_TG_CHAT_ID'],
    'limit'  => 1,
])->fetch();

if (!$userRow) {
    tgSendMessage($chatId, 'Токен не найден. Сформируйте новую ссылку в личном кабинете.', $botToken);
    http_response_code(200);
    die();
}

// Проверка срока действия токена
$expireField = $userRow['UF_TG_TOKEN_EXPIRE'];
if ($expireField instanceof DateTime && $expireField->getTimestamp() < time()) {
    tgSendMessage($chatId, 'Ссылка устарела. Сформируйте новую в личном кабинете.', $botToken);
    http_response_code(200);
    die();
}

// Токен уже был использован (chat_id уже привязан к этому пользователю)
if ((int)$userRow['UF_TG_CHAT_ID'] === $chatId) {
    tgSendMessage($chatId, 'Telegram уже подключён к вашему аккаунту CHOKERZ ✅', $botToken);
    http_response_code(200);
    die();
}

// Сохраняем chat_id, сбрасываем токен
$result = UserTable::update((int)$userRow['ID'], [
    'UF_TG_CHAT_ID'      => (string)$chatId,
    'UF_TG_TOKEN'        => null,
    'UF_TG_TOKEN_EXPIRE' => null,
]);

if ($result->isSuccess()) {
    $name = htmlspecialcharsEx($userRow['NAME'] ?? 'покупатель');
    tgSendMessage(
        $chatId,
        "✅ {$name}, Telegram подключён!\nТеперь вы будете получать уведомления о статусах заказов CHOKERZ.",
        $botToken
    );
} else {
    tgSendMessage($chatId, 'Произошла ошибка. Попробуйте позже.', $botToken);
}

http_response_code(200);

/**
 * Отправка сообщения через Telegram Bot API
 */
function tgSendMessage(int $chatId, string $text, string $botToken): void
{
    if ($chatId === 0 || $botToken === '') {
        return;
    }

    $url     = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $payload = json_encode(['chat_id' => $chatId, 'text' => $text], JSON_UNESCAPED_UNICODE);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\nContent-Length: " . strlen($payload),
            'content' => $payload,
            'timeout' => 5,
        ],
    ]);

    @file_get_contents($url, false, $ctx);
}
