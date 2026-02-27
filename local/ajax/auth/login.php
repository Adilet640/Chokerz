<?php
/**
 * AJAX: /local/ajax/auth/login.php
 * Авторизация пользователя через стандартный CUser::Login()
 * Принимает: POST USER_LOGIN, USER_PASSWORD
 * Возвращает: JSON {success, redirect?, errors?}
 */

declare(strict_types=1);

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

$siteRoot = $_SERVER['DOCUMENT_ROOT'];
require_once $siteRoot . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Application;
use Bitrix\Main\Web\Json;

header('Content-Type: application/json; charset=utf-8');

// --- Защита: только POST + проверка сессионного ключа ---
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
    || !check_bitrix_sessid()
) {
    http_response_code(403);
    echo Json::encode(['success' => false, 'errors' => ['Forbidden']]);
    die();
}

// --- Защита: только AJAX-запросы ---
$request = Application::getInstance()->getContext()->getRequest();
if (!$request->isAjaxRequest()) {
    http_response_code(400);
    echo Json::encode(['success' => false, 'errors' => ['AJAX only']]);
    die();
}

global $USER;

$login    = trim((string)($request->getPost('USER_LOGIN') ?? ''));
$password = (string)($request->getPost('USER_PASSWORD') ?? '');

$errors = [];

// --- Серверная валидация ---
if (empty($login)) {
    $errors['USER_LOGIN'] = 'Введите e-mail';
}

if (empty($password)) {
    $errors['USER_PASSWORD'] = 'Введите пароль';
}

if (!empty($errors)) {
    echo Json::encode(['success' => false, 'field_errors' => $errors]);
    die();
}

// --- Авторизация через стандартный CUser::Login ---
$authResult = $USER->Login($login, $password, 'Y');

if ($authResult === true) {
    // Определяем редирект: если пришли с ЛК — туда, иначе refresh
    $redirect = '/personal/';
    echo Json::encode(['success' => true, 'redirect' => $redirect]);
} else {
    // CUser::Login возвращает массив ошибок при неуспехе
    $errorMessage = is_array($authResult) ? ($authResult['MESSAGE'] ?? 'Ошибка авторизации') : 'Ошибка авторизации';
    echo Json::encode([
        'success' => false,
        'errors'  => [$errorMessage],
    ]);
}

die();
