<?php
/**
 * Компонент дашборда личного кабинета CHOKERZ
 * Выводит: приветствие, последний заказ, избранное (превью), адреса
 *
 * @package chokerz
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

class LkDashboardComponent extends CBitrixComponent
{
    /**
     * @var array Данные текущего пользователя
     */
    private array $currentUser = [];

    public function onPrepareComponentParams($arParams): array
    {
        // Лимит товаров в превью избранного
        $arParams['WISHLIST_PREVIEW_COUNT'] = (int)($arParams['WISHLIST_PREVIEW_COUNT'] ?? 3);
        // ID инфоблока каталога
        $arParams['IBLOCK_ID'] = (int)($arParams['IBLOCK_ID'] ?? 0);
        // Highload-блок избранного (символьный код)
        $arParams['WISHLIST_HL_CODE'] = $arParams['WISHLIST_HL_CODE'] ?? 'Wishlist';

        return $arParams;
    }

    public function executeComponent(): void
    {
        global $USER;

        // Редирект неавторизованных
        if (!$USER->IsAuthorized()) {
            LocalRedirect(SITE_DIR . 'personal/login/?back_url=' . urlencode($_SERVER['REQUEST_URI']));
        }

        if (!Loader::includeModule('sale') || !Loader::includeModule('iblock') || !Loader::includeModule('highloadblock')) {
            ShowError('Не подключены модули: sale, iblock, highloadblock');
            return;
        }

        $userId = (int)$USER->GetID();

        $cacheId  = 'lk_dashboard_' . $userId;
        $cacheDir = '/lk/dashboard/' . $userId;
        $cache    = \Bitrix\Main\Data\Cache::createInstance();

        if ($cache->initCache(600, $cacheId, $cacheDir)) {
            $cachedData       = $cache->getVars();
            $this->arResult   = $cachedData;
        } elseif ($cache->startDataCache()) {
            $this->arResult = [
                'USER'          => $this->fetchUser($userId),
                'LAST_ORDER'    => $this->fetchLastOrder($userId),
                'WISHLIST'      => $this->fetchWishlistPreview($userId),
                'ADDRESSES'     => $this->fetchAddresses($userId),
                'STATS'         => $this->fetchStats($userId),
            ];
            $cache->endDataCache($this->arResult);
        }

        $this->IncludeComponentTemplate();
    }

    /**
     * Данные пользователя из UserTable (D7)
     */
    private function fetchUser(int $userId): array
    {
        $row = UserTable::getList([
            'filter' => ['=ID' => $userId, '=ACTIVE' => 'Y'],
            'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL', 'PERSONAL_PHONE', 'PERSONAL_BIRTHDAY', 'PERSONAL_PHOTO'],
            'limit'  => 1,
        ])->fetch();

        if (!$row) {
            return [];
        }

        $row['DISPLAY_NAME'] = trim(($row['NAME'] ?? '') . ' ' . ($row['LAST_NAME'] ?? ''));
        if ($row['DISPLAY_NAME'] === '') {
            $row['DISPLAY_NAME'] = $row['EMAIL'];
        }

        return $row;
    }

    /**
     * Последний заказ пользователя (D7 Sale ORM)
     */
    private function fetchLastOrder(int $userId): array
    {
        $dbOrder = OrderTable::getList([
            'filter'  => ['=USER_ID' => $userId],
            'select'  => ['ID', 'ACCOUNT_NUMBER', 'DATE_INSERT', 'PRICE', 'CURRENCY', 'STATUS_ID', 'PAYED', 'DEDUCTED'],
            'order'   => ['DATE_INSERT' => 'DESC'],
            'limit'   => 1,
        ])->fetch();

        if (!$dbOrder) {
            return [];
        }

        // Товары последнего заказа
        $order = Order::load($dbOrder['ID']);
        $items = [];

        if ($order) {
            $basket = $order->getBasket();
            foreach ($basket as $basketItem) {
                $items[] = [
                    'NAME'     => $basketItem->getField('NAME'),
                    'QUANTITY' => $basketItem->getQuantity(),
                    'PRICE'    => $basketItem->getPrice(),
                    'CURRENCY' => $basketItem->getCurrency(),
                    'PRODUCT_ID' => $basketItem->getProductId(),
                    'DETAIL_PAGE_URL' => $basketItem->getField('DETAIL_PAGE_URL'),
                    'PROPS'    => $this->getBasketItemProps($basketItem),
                ];
            }

            // Доставка
            $shipmentCollection = $order->getShipmentCollection();
            $deliveryName = '';
            foreach ($shipmentCollection as $shipment) {
                if (!$shipment->isSystem()) {
                    $deliveryName = $shipment->getDeliveryName();
                    break;
                }
            }

            $dbOrder['DELIVERY_NAME'] = $deliveryName;
        }

        $dbOrder['ITEMS']       = $items;
        $dbOrder['DATE_FORMAT'] = $dbOrder['DATE_INSERT'] instanceof DateTime
            ? $dbOrder['DATE_INSERT']->format('d.m.Y')
            : '';

        // Статус заказа: локализованное название
        $dbOrder['STATUS_NAME'] = $this->getOrderStatusName($dbOrder['STATUS_ID']);

        return $dbOrder;
    }

    /**
     * Свойства позиции корзины (размер, цвет)
     */
    private function getBasketItemProps(\Bitrix\Sale\BasketItem $item): array
    {
        $props = [];
        $propCollection = $item->getPropertyCollection();
        if ($propCollection) {
            foreach ($propCollection as $prop) {
                $props[] = [
                    'NAME'  => $prop->getField('NAME'),
                    'VALUE' => $prop->getField('VALUE'),
                    'CODE'  => $prop->getField('CODE'),
                ];
            }
        }
        return $props;
    }

    /**
     * Название статуса заказа
     */
    private function getOrderStatusName(string $statusId): string
    {
        $statuses = \Bitrix\Sale\OrderStatus::getAllStatuses();
        return $statuses[$statusId]['NAME'] ?? $statusId;
    }

    /**
     * Превью избранного из Highload-блока Wishlist
     */
    private function fetchWishlistPreview(int $userId): array
    {
        try {
            $hlBlock = \Bitrix\Highloadblock\HighloadBlockTable::getList([
                'filter' => ['=NAME' => $this->arParams['WISHLIST_HL_CODE']],
                'limit'  => 1,
            ])->fetch();

            if (!$hlBlock) {
                return [];
            }

            $entity   = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlBlock);
            $entityDC = $entity->getDataClass();

            $rows = $entityDC::getList([
                'filter' => ['=UF_USER_ID' => $userId],
                'select' => ['ID', 'UF_PRODUCT_ID'],
                'order'  => ['ID' => 'DESC'],
                'limit'  => $this->arParams['WISHLIST_PREVIEW_COUNT'],
            ])->fetchAll();

            if (empty($rows)) {
                return [];
            }

            $productIds = array_column($rows, 'UF_PRODUCT_ID');
            return $this->fetchProductsByIds($productIds);

        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Базовые данные товаров по ID (для превью)
     */
    private function fetchProductsByIds(array $ids): array
    {
        if (empty($ids) || !$this->arParams['IBLOCK_ID']) {
            return [];
        }

        $result = [];
        $res = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => $this->arParams['IBLOCK_ID'], 'ID' => $ids, 'ACTIVE' => 'Y'],
            false,
            false,
            ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'PROPERTY_PRICE_MIN', 'PROPERTY_PRICE_MAX']
        );

        while ($row = $res->GetNextElement()) {
            $fields = $row->GetFields();
            $props  = $row->GetProperties();

            $previewSrc = '';
            if ($fields['PREVIEW_PICTURE']) {
                $file = \CFile::GetFileArray($fields['PREVIEW_PICTURE']);
                $previewSrc = $file ? \CFile::GetFileSRC($file) : '';
            }

            $result[] = [
                'ID'              => $fields['ID'],
                'NAME'            => $fields['NAME'],
                'DETAIL_PAGE_URL' => $fields['DETAIL_PAGE_URL'],
                'PREVIEW_SRC'     => $previewSrc,
            ];
        }

        return $result;
    }

    /**
     * Адреса доставки из стандартного Битрикс (sale_order_props_user_profile)
     */
    private function fetchAddresses(int $userId): array
    {
        $result = [];
        $res    = CSaleOrderUserProps::GetList(
            ['ID' => 'DESC'],
            ['USER_ID' => $userId],
            false,
            ['nTopCount' => 3]
        );

        while ($row = $res->Fetch()) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Статистика: количество заказов, сумма, избранное
     */
    private function fetchStats(int $userId): array
    {
        $ordersCount = OrderTable::getList([
            'filter' => ['=USER_ID' => $userId],
            'count_total' => true,
        ])->getCount();

        return [
            'ORDERS_COUNT' => $ordersCount,
        ];
    }
}
