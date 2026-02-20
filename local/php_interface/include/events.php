<?php
/**
 * Обработчики событий проекта CHOKERZ
 */

use Bitrix\Main\EventManager;

$eventManager = EventManager::getInstance();

/**
 * Событие: перед добавлением элемента в корзину
 */
$eventManager->addEventHandler(
    'sale',
    'OnBeforeBasketAdd',
    'onBeforeBasketAddHandler'
);

function onBeforeBasketAddHandler($basketCode, $productId, $quantity) {
    // Можно добавить логику проверки товара перед добавлением в корзину
    // Например, проверка наличия, ограничение количества и т.д.
    
    // Вернуть true для разрешения добавления
    return true;
}

/**
 * Событие: после добавления заказа
 */
$eventManager->addEventHandler(
    'sale',
    'OnSaleOrderSaved',
    'onSaleOrderSavedHandler'
);

function onSaleOrderSavedHandler($order) {
    // Логика после сохранения заказа
    // Например, отправка уведомлений, обновление статистики и т.д.
    
    // Логирование заказа
    \CEventLog::Add(array(
        'SEVERITY' => 'INFO',
        'AUDIT_TYPE_ID' => 'ORDER_CREATED',
        'MODULE_ID' => 'sale',
        'ITEM_ID' => $order->getId(),
        'DESCRIPTION' => 'Заказ #' . $order->getId() . ' успешно создан'
    ));
    
    // Здесь можно добавить отправку уведомления в Telegram
    // или другие действия
}

/**
 * Событие: перед выводом элемента каталога
 */
$eventManager->addEventHandler(
    'iblock',
    'OnBeforeIBlockElementShow',
    'onBeforeIBlockElementShowHandler'
);

function onBeforeIBlockElementShowHandler($elementId, $iblockId) {
    // Можно добавить логику перед выводом элемента
    // Например, увеличение счётчика просмотров
    return $elementId;
}

/**
 * Событие: после регистрации пользователя
 */
$eventManager->addEventHandler(
    'main',
    'OnAfterUserRegister',
    'onAfterUserRegisterHandler'
);

function onAfterUserRegisterHandler($arFields) {
    // Логика после регистрации пользователя
    // Например, отправка приветственного письма
    
    // Логирование
    \CEventLog::Add(array(
        'SEVERITY' => 'INFO',
        'AUDIT_TYPE_ID' => 'USER_REGISTERED',
        'MODULE_ID' => 'main',
        'ITEM_ID' => $arFields['ID'],
        'DESCRIPTION' => 'Пользователь ' . $arFields['EMAIL'] . ' успешно зарегистрирован'
    ));
}

/**
 * Событие: изменение цены товара
 */
$eventManager->addEventHandler(
    'catalog',
    'OnBeforePriceUpdate',
    'onBeforePriceUpdateHandler'
);

function onBeforePriceUpdateHandler($priceId, $fields) {
    // Можно добавить логику перед обновлением цены
    // Например, проверка корректности цены
    return true;
}

/**
 * Событие: перед отправкой почтового события
 */
$eventManager->addEventHandler(
    'main',
    'OnBeforeEventSend',
    'onBeforeEventSendHandler'
);

function onBeforeEventSendHandler($arFields) {
    // Можно модифицировать поля почтового события
    // или добавить дополнительные данные
    
    // Например, добавление информации о сайте
    $arFields['SITE_NAME'] = 'CHOKERZ — амуниция для животных';
    
    return $arFields;
}
