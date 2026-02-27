<?php
/**
 * Компонент: chokerz:modal.manager
 * Назначение: рендер оверлея и всех модальных окон сайта (auth, address, review, warranty)
 * Директория: /local/components/chokerz/modal.manager/
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Application;

/** @var CBitrixComponent $this */

Loader::includeModule('iblock');
Loader::includeModule('sale');

$arResult = [];

// --- Данные для формы авторизации ---
// Передаём только флаги состояния. Логика авторизации — через стандартный компонент system.auth.form
$arResult['AUTH'] = [
    'IS_AUTHORIZED' => $USER->IsAuthorized(),
    'USER_NAME'     => $USER->IsAuthorized() ? htmlspecialcharsbx($USER->GetFullName() ?: $USER->GetLogin()) : '',
];

// --- Социальные сети для OAuth (конфиг из настроек компонента) ---
$arResult['SOCIAL_SERVICES'] = $arParams['SOCIAL_SERVICES'] ?? ['vk', 'ok', 'tg'];

$this->arResult = $arResult;
$this->IncludeComponentTemplate();
