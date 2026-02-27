<?php
/**
 * Шаблон компонента sale.personal.order.list
 * Путь: /local/templates/chokerz/components/bitrix/sale.personal.order.list/.default/template.php
 * Отображает список заказов пользователя в ЛК
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult Результаты компонента sale.personal.order.list */
$orders = $arResult['ORDERS'] ?? [];

/**
 * Словарь CSS-модификаторов статусов
 * Ключи = STATUS_ID из Битрикс
 */
$statusMod = [
    'N' => 'new',
    'P' => 'paid',
    'A' => 'assembly',
    'D' => 'delivered',
    'F' => 'complete',
    'C' => 'cancelled',
];
?>

<div class="lk-orders" data-orders-list>

    <div class="lk-page-head">
        <h1 class="lk-page-head__title">Мои заказы</h1>
        <?php if (!empty($orders)): ?>
        <span class="lk-page-head__count"><?= count($orders) ?></span>
        <?php endif; ?>
    </div>

    <!-- Фильтр по статусам -->
    <div class="lk-orders__filter lk-order-filter" role="group" aria-label="Фильтр заказов">
        <button class="lk-order-filter__btn lk-order-filter__btn--active" data-filter-status="all" type="button">Все</button>
        <button class="lk-order-filter__btn" data-filter-status="N" type="button">Новые</button>
        <button class="lk-order-filter__btn" data-filter-status="P" type="button">Оплачены</button>
        <button class="lk-order-filter__btn" data-filter-status="D" type="button">Доставляются</button>
        <button class="lk-order-filter__btn" data-filter-status="F" type="button">Завершены</button>
        <button class="lk-order-filter__btn" data-filter-status="C" type="button">Отменены</button>
    </div>

    <?php if (empty($orders)): ?>
    <!-- Пустое состояние -->
    <div class="lk-orders__empty lk-empty-state">
        <svg class="lk-empty-state__icon" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true">
            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
            <rect x="9" y="3" width="6" height="4" rx="2"/>
        </svg>
        <p class="lk-empty-state__text">Заказов ещё нет</p>
        <a href="/catalog/" class="btn btn--primary lk-empty-state__btn">Перейти в каталог</a>
    </div>

    <?php else: ?>

    <ul class="lk-orders__list" aria-label="Список заказов">

        <?php foreach ($orders as $order):
            $statusId  = $order['STATUS_ID'] ?? 'N';
            $statusCss = $statusMod[$statusId] ?? 'new';
            $orderId   = (int)$order['ID'];
            $orderNum  = htmlspecialcharsEx($order['ACCOUNT_NUMBER'] ?? $orderId);
            $dateIns   = htmlspecialcharsEx($order['DATE_INSERT_FORMATTED'] ?? '');
            $price     = number_format((float)($order['PRICE'] ?? 0), 0, '.', ' ');
            $statusName = htmlspecialcharsEx($order['STATUS_NAME'] ?? $statusId);
            $payed     = $order['PAYED'] === 'Y';
        ?>

        <li class="lk-orders__item lk-order-card" data-order-status="<?= htmlspecialcharsEx($statusId) ?>">

            <div class="lk-order-card__header">
                <div class="lk-order-card__meta">
                    <span class="lk-order-card__number">Заказ №<?= $orderNum ?></span>
                    <span class="lk-order-card__date"><?= $dateIns ?></span>
                </div>
                <div class="lk-order-card__badges">
                    <span class="lk-order-status lk-order-status--<?= $statusCss ?>"><?= $statusName ?></span>
                    <?php if ($payed): ?>
                    <span class="lk-order-badge lk-order-badge--paid">Оплачен</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Товары заказа (краткий список) -->
            <?php if (!empty($order['BASKET_ITEMS'])): ?>
            <div class="lk-order-card__items lk-order-items">
                <?php
                $maxVisible = 2;
                $items = $order['BASKET_ITEMS'];
                $total = count($items);
                $shown = array_slice($items, 0, $maxVisible);
                ?>
                <?php foreach ($shown as $item): ?>
                <div class="lk-order-item">
                    <span class="lk-order-item__name"><?= htmlspecialcharsEx($item['NAME']) ?></span>
                    <span class="lk-order-item__qty"><?= (int)$item['QUANTITY'] ?> шт.</span>
                    <span class="lk-order-item__price"><?= number_format((float)$item['PRICE'], 0, '.', ' ') ?> ₽</span>
                </div>
                <?php endforeach; ?>
                <?php if ($total > $maxVisible): ?>
                <span class="lk-order-items__more">+ ещё <?= $total - $maxVisible ?> товар(а)</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="lk-order-card__footer">
                <span class="lk-order-card__total">
                    Итого: <span class="lk-order-card__total-price"><?= $price ?> ₽</span>
                </span>
                <a href="/personal/order/detail/<?= $orderId ?>/" class="btn btn--outline lk-order-card__btn">
                    Подробнее
                </a>
            </div>

        </li>

        <?php endforeach; ?>
    </ul>

    <?php endif; ?>

</div>
