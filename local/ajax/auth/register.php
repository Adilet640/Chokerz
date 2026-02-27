<?php
/**
 * AJAX: /local/ajax/auth/register.php
 * Регистрация пользователя через стандартный CUser::Register()
 * Принимает: POST NAME, EMAIL, PERSONAL_PHONE, PASSWORD, CONFIRM_PASSWORD, AGREE
 * Возвращает: JSON {success, redirect?, field_errors?, errors?}
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
use Bitrix\Main\Mail\Event as MailEvent;

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

// --- Сбор данных ---
$name            = trim((string)($request->getPost('NAME') ?? ''));
$email           = trim((string)($request->getPost('EMAIL') ?? ''));
$phone           = trim((string)($request->getPost('PERSONAL_PHONE') ?? ''));
$password        = (string)($request->getPost('PASSWORD') ?? '');
$confirmPassword = (string)($request->getPost('CONFIRM_PASSWORD') ?? '');
$agree           = (string)($request->getPost('AGREE') ?? '');

// --- Серверная валидация ---
$errors = [];

if (empty($name)) {
    $errors['NAME'] = 'Введите имя';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['EMAIL'] = 'Введите корректный e-mail';
}

if (strlen($password) < 8) {
    $errors['PASSWORD'] = 'Пароль должен содержать не менее 8 символов';
}

if ($password !== $confirmPassword) {
    $errors['CONFIRM_PASSWORD'] = 'Пароли не совпадают';
}

if ($agree !== 'Y') {
    $errors['AGREE'] = 'Необходимо согласиться с политикой конфиденциальности';
}

if (!empty($errors)) {
    echo Json::encode(['success' => false, 'field_errors' => $errors]);
    die();
}

// --- Регистрация через стандартный CUser::Register ---
$newUser = new CUser();
$regResult = $newUser->Register(
    $email,          // login = email
    $name,           // first name
    '',              // last name
    $password,
    $confirmPassword,
    $email,
    SITE_ID
);

if ($regResult['TYPE'] === 'OK') {
    // Привязываем телефон отдельно через Update, т.к. Register не принимает PERSONAL_PHONE
    if (!empty($phone)) {
        $newUser->Update($USER->GetID(), ['PERSONAL_PHONE' => $phone]);
    }

    // Автоматически авторизуем
    $USER->Login($email, $password);

    echo Json::encode(['success' => true, 'redirect' => '/personal/']);
} else {
    $errorMessage = is_array($regResult) ? ($regResult['MESSAGE'] ?? 'Ошибка регистрации') : 'Ошибка регистрации';
    echo Json::encode([
        'success' => false,
        'errors'  => [$errorMessage],
    ]);
}

die();
