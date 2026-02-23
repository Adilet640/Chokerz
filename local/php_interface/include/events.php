use Bitrix\Main\EventManager;

$eventManager = EventManager::getInstance();

/**
 * Событие: после сохранения заказа
 * Логируем создание заказа, можно добавить Telegram-уведомление.
 */
$eventManager->addEventHandler(
    'sale',
    'OnSaleOrderSaved',
    [ChokerzEventHandlers::class, 'onOrderSaved']
);

/**
 * Событие: после регистрации пользователя
 */
$eventManager->addEventHandler(
    'main',
    'OnAfterUserRegister',
    [ChokerzEventHandlers::class, 'onUserRegister']
);

/**
 * Событие: перед отправкой почтового события
 * ВАЖНО: обработчик должен возвращать $arFields, иначе изменения теряются.
 */
$eventManager->addEventHandler(
    'main',
    'OnBeforeEventSend',
    [ChokerzEventHandlers::class, 'onBeforeMailSend']
);

/**
 * Событие: изменение элемента инфоблока — сброс управляемого кэша
 * Это ключевой обработчик для актуальности карточек товаров.
 */
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

        // TODO: здесь добавить отправку Telegram-уведомления через webhook (ТЗ п.7.3)
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
            'DESCRIPTION'   => 'Зарегистрирован пользователь: ' . ($arFields['EMAIL'] ?? ''),
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
     * @param array $arFields Поля элемента
     */
    public static function onIBlockElementUpdate(array $arFields): void
    {
        if (empty($arFields['ID'])) {
            return;
        }

        // Сброс кэша конкретного элемента по тегу
        \CBitrixComponent::clearComponentCache('custom:catalog.item', '/');
    }
}
