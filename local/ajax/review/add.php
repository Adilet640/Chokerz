<?php
/**
 * AJAX: /local/ajax/review/add.php
 * Добавление отзыва к товару.
 * Хранение: Highload-блок "Reviews" (рекомендуется для большого объёма — см. ТЗ п. 7.4)
 *
 * Структура HL-блока "Reviews" (создать в админке Битрикс):
 *   UF_PRODUCT_ID   — Integer, ID товара каталога
 *   UF_USER_ID      — Integer, ID пользователя (0 если анонимно)
 *   UF_AUTHOR_NAME  — String
 *   UF_RATING       — Integer (1-5)
 *   UF_PROS         — String
 *   UF_CONS         — String
 *   UF_TEXT         — Text
 *   UF_PHOTOS       — File (multiple)
 *   UF_ACTIVE       — Boolean (по умолчанию false — на модерации)
 *   UF_SOURCE       — String: 'site' | 'ozon' | 'wb'
 *   UF_SOURCE_ID    — String: внешний ID отзыва (для дедупликации)
 *   UF_DATE_CREATED — DateTime
 *
 * Принимает: POST PRODUCT_ID, RATING, AUTHOR_NAME, PROS, CONS, TEXT + FILES PHOTOS[]
 * Возвращает: JSON {success, review_id?, errors?}
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
use Bitrix\Main\Type\DateTime;

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

Loader::includeModule('iblock');
Loader::includeModule('highloadblock');

global $USER;

$isAuth    = $USER->IsAuthorized();
$userId    = $isAuth ? (int)$USER->GetID() : 0;

// --- Сбор данных ---
$productId   = (int)($request->getPost('PRODUCT_ID') ?? 0);
$rating      = (int)($request->getPost('RATING') ?? 0);
$authorName  = trim((string)($request->getPost('AUTHOR_NAME') ?? ($isAuth ? $USER->GetFullName() : '')));
$pros        = trim((string)($request->getPost('PROS') ?? ''));
$cons        = trim((string)($request->getPost('CONS') ?? ''));
$text        = trim((string)($request->getPost('TEXT') ?? ''));

// --- Серверная валидация ---
$fieldErrors = [];

if ($productId <= 0) {
    $fieldErrors['PRODUCT_ID'] = 'Не указан товар';
}
if ($rating < 1 || $rating > 5) {
    $fieldErrors['RATING'] = 'Выберите оценку от 1 до 5';
}
if (empty($authorName)) {
    $fieldErrors['AUTHOR_NAME'] = 'Введите имя';
}
if (empty($text)) {
    $fieldErrors['TEXT'] = 'Напишите текст отзыва';
}

if (!empty($fieldErrors)) {
    echo Json::encode(['success' => false, 'field_errors' => $fieldErrors]);
    die();
}

// --- Обработка фото ---
// Допустимые MIME: image/jpeg, image/webp
// PNG запрещён по ТЗ п. 6.3 (кроме прозрачности — но для отзывов не актуально)
$allowedMime  = ['image/jpeg', 'image/webp'];
$maxFileSize  = 5 * 1024 * 1024; // 5 МБ
$maxFiles     = 5;
$uploadedFiles = [];

if (!empty($_FILES['PHOTOS']['name'][0])) {
    $fileCount = count($_FILES['PHOTOS']['name']);
    if ($fileCount > $maxFiles) {
        echo Json::encode(['success' => false, 'field_errors' => ['PHOTOS' => "Максимум {$maxFiles} фото"]]);
        die();
    }

    for ($i = 0; $i < $fileCount; $i++) {
        if ($_FILES['PHOTOS']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        if ($_FILES['PHOTOS']['size'][$i] > $maxFileSize) {
            echo Json::encode(['success' => false, 'field_errors' => ['PHOTOS' => 'Размер файла превышает 5 МБ']]);
            die();
        }
        $mimeType = mime_content_type($_FILES['PHOTOS']['tmp_name'][$i]);
        if (!in_array($mimeType, $allowedMime, true)) {
            echo Json::encode(['success' => false, 'field_errors' => ['PHOTOS' => 'Допустимые форматы: WebP, JPG']]);
            die();
        }

        // Стандартная загрузка файла через Битрикс CFile::SaveFile
        $fileArray = [
            'name'     => $_FILES['PHOTOS']['name'][$i],
            'type'     => $_FILES['PHOTOS']['type'][$i],
            'tmp_name' => $_FILES['PHOTOS']['tmp_name'][$i],
            'error'    => $_FILES['PHOTOS']['error'][$i],
            'size'     => $_FILES['PHOTOS']['size'][$i],
        ];
        $fileId = CFile::SaveFile($fileArray, 'reviews');
        if ($fileId > 0) {
            $uploadedFiles[] = $fileId;
        }
    }
}

// --- HL-блок Reviews ---
$hlBlockId = (int)(defined('CHOKERZ_HL_REVIEWS_ID') ? CHOKERZ_HL_REVIEWS_ID : 0);

if ($hlBlockId <= 0) {
    echo Json::encode(['success' => false, 'errors' => ['Highload-блок Reviews не настроен. Установите константу CHOKERZ_HL_REVIEWS_ID в /local/php_interface/init.php']]);
    die();
}

$hlBlock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlBlockId)->fetch();
if (!$hlBlock) {
    echo Json::encode(['success' => false, 'errors' => ['HL-блок не найден']]);
    die();
}
$hlDataClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlBlock)->getDataClass();

$fields = [
    'UF_PRODUCT_ID'   => $productId,
    'UF_USER_ID'      => $userId,
    'UF_AUTHOR_NAME'  => htmlspecialcharsbx($authorName),
    'UF_RATING'       => $rating,
    'UF_PROS'         => htmlspecialcharsbx($pros),
    'UF_CONS'         => htmlspecialcharsbx($cons),
    'UF_TEXT'         => htmlspecialcharsbx($text),
    'UF_ACTIVE'       => false, // На модерацию
    'UF_SOURCE'       => 'site',
    'UF_SOURCE_ID'    => '',
    'UF_DATE_CREATED' => new DateTime(),
];

// Фото — UF_PHOTOS хранит как множественный файл (Multiple = Y в HL-блоке)
// CFile-файлы сохранены выше, передаём массив ID
if (!empty($uploadedFiles)) {
    $fields['UF_PHOTOS'] = $uploadedFiles;
}

$result = $hlDataClass::add($fields);

if ($result->isSuccess()) {
    // Пересчёт агрегированного рейтинга товара (обновляем свойство RATING в инфоблоке)
    // Запускаем через агент или событие, чтобы не замедлять ответ
    \Bitrix\Main\Application::getInstance()->addBackgroundJob(
        static function () use ($productId, $hlDataClass): void {
            $ratingData = $hlDataClass::getList([
                'filter' => ['UF_PRODUCT_ID' => $productId, 'UF_ACTIVE' => true, 'UF_SOURCE' => 'site'],
                'select' => ['UF_RATING'],
            ]);
            $ratings = [];
            while ($row = $ratingData->fetch()) {
                $ratings[] = (int)$row['UF_RATING'];
            }
            if (!empty($ratings)) {
                $avg = round(array_sum($ratings) / count($ratings), 1);
                // Обновляем свойство RATING элемента каталога
                CIBlockElement::SetPropertyValuesEx($productId, false, ['RATING' => $avg]);
            }
        }
    );

    echo Json::encode(['success' => true, 'review_id' => $result->getId()]);
} else {
    echo Json::encode(['success' => false, 'errors' => $result->getErrorMessages()]);
}

die();
