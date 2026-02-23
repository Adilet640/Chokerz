<?php
/**
 * events.php — обработчики событий CHOKERZ
 *
 * Изменения (2026-02-23):
 *  - Удалено несуществующее событие 'OnBeforeIBlockElementShow'
 *  - Добавлен return в onBeforeMailSend (без него изменения $arFields теряются)
 *  - Обработчики переведены в класс ChokerzEventHandlers (нет конфликтов глобальных имён)
 *  - Добавлен обработчик сброса управляемого кэша при изменении элемента инфоблока
 *
 * Путь: local/php_interface/include/events.php
 *
 * @package CHOKERZ
 * @version 1.1
 */

use Bitrix\Main\EventManager;

$eventManager = EventManager::getInstance();

// После сохранения заказа
$eventManager->addEventHandler(
    'sale',
    'OnSaleOrderSaved',
    [ChokerzEventHandlers::class, 'onOrderSaved']
);

// После регистрации пользователя
$eventManager->addEventHandler(
    'main',
    'OnAfterUserRegister',
    [ChokerzEventHandlers::class, 'onUserRegister']
);

// Перед отправкой почтового события
$eventManager->addEventHandler(
    'main',
    'OnBeforeEventSend',
    [ChokerzEventHandlers::class, 'onBeforeMailSend']
);

// Сброс кэша при изменении элемента инфоблока
$eventManager->addEventHandler(
    'iblock',
    'OnAfterIBlockElementUpdate',
    [ChokerzEventHandlers::class, 'onIBlockElementUpdate']
);

$eventManager->addEventHandler(
    'iblock',
    'OnAfterIBlockElementAdd',
    [ChokerzEventHandlers::class, 'onIBlockElementUpdate']
);

/**
 * Класс-контейнер обработчиков событий.
 * Использование class вместо глобальных функций исключает конфликты имён.
 */
class ChokerzEventHandlers
{
    /**
     * Логирование созданного заказа.
     *
     * @param \Bitrix\Sale\Order $order
     */
    public static function onOrderSaved(\Bitrix\Sale\Order $order): void
    {
        \CEventLog::Add([
            'SEVERITY'      => 'INFO',
            'AUDIT_TYPE_ID' => 'CHOKERZ_ORDER_CREATED',
            'MODULE_ID'     => 'sale',
            'ITEM_ID'       => $order->getId(),
            'DESCRIPTION'   => 'Заказ #' . $order->getId() . ' создан',
        ]);

        // TODO: Telegram-уведомление через webhook (ТЗ п.7.3)
    }

    /**
     * Логирование регистрации пользователя.
     *
     * @param array $arFields
     */
    public static function onUserRegister(array $arFields): void
    {
        \CEventLog::Add([
            'SEVERITY'      => 'INFO',
            'AUDIT_TYPE_ID' => 'CHOKERZ_USER_REGISTER',
            'MODULE_ID'     => 'main',
            'ITEM_ID'       => $arFields['ID'] ?? 0,
            'DESCRIPTION'   => 'Зарегистрирован: ' . ($arFields['EMAIL'] ?? ''),
        ]);
    }

    /**
     * Модификация полей почтового события.
     * ОБЯЗАТЕЛЬНО возвращает $arFields — иначе изменения теряются.
     *
     * @param  array $arFields
     * @return array
     */
    public static function onBeforeMailSend(array $arFields): array
    {
        $arFields['SITE_NAME'] = 'CHOKERZ — амуниция для животных';
        return $arFields;
    }

    /**
     * Сброс управляемого кэша при изменении элемента инфоблока.
     * Обеспечивает актуальность данных карточки товара без ручного сброса.
     *
     * @param array $arFields
     */
    public static function onIBlockElementUpdate(array $arFields): void
    {
        if (empty($arFields['ID'])) {
            return;
        }

        \CBitrixComponent::clearComponentCache('custom:catalog.item', '/');
    }
}
