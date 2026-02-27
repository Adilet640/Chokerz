<?php
/**
 * Шаблон компонента sale.personal.order.detail
 * Путь: /local/templates/chokerz/components/bitrix/sale.personal.order.detail/.default/template.php
 * Детальная страница заказа в ЛК
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */
$order    = $arResult['ORDER']    ?? [];
$items    = $arResult['BASKET']   ?? [];
$shipments = $arResult['SHIPMENT'] ?? [];
$payments  = $arResult['PAYMENT']  ?? [];

if (empty($order)) {
    ?>
    <div class="lk-order-detail lk-empty-state">
        <p class="lk-empty-state__text">Заказ не найден.</p>
        <a href="/personal/order/list/" class="btn btn--outline">Вернуться к заказам</a>
    </div>
    <?php
    return;
}

$statusMod = ['N'=>'new','P'=>'paid','A'=>'assembly','D'=>'delivered','F'=>'complete','C'=>'cancelled'];
$statusId  = $order['STATUS_ID'] ?? 'N';
$statusCss = $statusMod[$statusId] ?? 'new';
?>

<div class="lk-order-detail">

    <!-- ШАПКА -->
    <div class="lk-page-head lk-order-detail__head">
        <div class="lk-page-head__breadcrumb">
            <a href="/personal/order/list/" class="lk-breadcrumb__link">Мои заказы</a>
            <span class="lk-breadcrumb__sep" aria-hidden="true">/</span>
            <span class="lk-breadcrumb__current">Заказ №<?= htmlspecialcharsEx($order['ACCOUNT_NUMBER']) ?></span>
        </div>
        <span class="lk-order-status lk-order-status--<?= $statusCss ?>">
            <?= htmlspecialcharsEx($order['STATUS_NAME'] ?? $statusId) ?>
        </span>
    </div>

    <!-- ИСТОРИЯ СТАТУСОВ / ТРЕКИНГ -->
    <?php if (!empty($arResult['HISTORY'])): ?>
    <div class="lk-order-detail__track lk-order-track" aria-label="История статусов">
        <ol class="lk-order-track__list">
            <?php foreach ($arResult['HISTORY'] as $historyItem): ?>
            <li class="lk-order-track__item">
                <span class="lk-order-track__date"><?= htmlspecialcharsEx($historyItem['DATE_INSERT_FORMATTED'] ?? '') ?></span>
                <span class="lk-order-track__status"><?= htmlspecialcharsEx($historyItem['STATUS_NAME'] ?? '') ?></span>
                <?php if (!empty($historyItem['COMMENT'])): ?>
                <span class="lk-order-track__comment"><?= htmlspecialcharsEx($historyItem['COMMENT']) ?></span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
    <?php endif; ?>

    <div class="lk-order-detail__grid">

        <!-- СОСТАВ ЗАКАЗА -->
        <div class="lk-order-detail__main">

            <div class="lk-order-section">
                <h2 class="lk-order-section__title">Состав заказа</h2>

                <?php if (!empty($items)): ?>
                <ul class="lk-order-detail__items lk-order-items--detail" aria-label="Товары заказа">
                    <?php foreach ($items as $item):
                        $imgSrc = '';
                        if (!empty($item['PREVIEW_PICTURE'])) {
                            $file = \CFile::GetFileArray($item['PREVIEW_PICTURE']);
                            $imgSrc = $file ? \CFile::GetFileSRC($file) : '';
                        }
                    ?>
                    <li class="lk-order-item lk-order-item--detail">
                        <?php if ($imgSrc): ?>
                        <a href="<?= htmlspecialcharsEx($item['DETAIL_PAGE_URL'] ?? '#') ?>" class="lk-order-item__img-wrap">
                            <img
                                src="<?= htmlspecialcharsEx($imgSrc) ?>"
                                alt="<?= htmlspecialcharsEx($item['NAME']) ?>"
                                class="lk-order-item__img"
                                loading="lazy"
                                width="80"
                                height="80"
                            >
                        </a>
                        <?php endif; ?>

                        <div class="lk-order-item__body">
                            <a href="<?= htmlspecialcharsEx($item['DETAIL_PAGE_URL'] ?? '#') ?>" class="lk-order-item__name">
                                <?= htmlspecialcharsEx($item['NAME']) ?>
                            </a>

                            <?php if (!empty($item['PROPS'])): ?>
                            <ul class="lk-order-item__props" aria-label="Характеристики">
                                <?php foreach ($item['PROPS'] as $prop): ?>
                                <li class="lk-order-item__prop">
                                    <?= htmlspecialcharsEx($prop['NAME']) ?>: <?= htmlspecialcharsEx($prop['VALUE']) ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>

                        <div class="lk-order-item__footer">
                            <span class="lk-order-item__qty"><?= (int)$item['QUANTITY'] ?> шт.</span>
                            <span class="lk-order-item__price"><?= number_format((float)$item['PRICE'], 0, '.', ' ') ?> ₽</span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>

        </div>

        <!-- БОКОВЫЕ ДАННЫЕ: оплата, доставка, итог -->
        <aside class="lk-order-detail__aside">

            <!-- Итог -->
            <div class="lk-order-section lk-order-section--sum">
                <h2 class="lk-order-section__title">Итог</h2>
                <dl class="lk-order-sum">
                    <div class="lk-order-sum__row">
                        <dt class="lk-order-sum__label">Товары</dt>
                        <dd class="lk-order-sum__value"><?= number_format((float)($order['PRICE_DELIVERY'] ? $order['PRICE'] - $order['PRICE_DELIVERY'] : $order['PRICE']), 0, '.', ' ') ?> ₽</dd>
                    </div>
                    <?php if (!empty($order['PRICE_DELIVERY'])): ?>
                    <div class="lk-order-sum__row">
                        <dt class="lk-order-sum__label">Доставка</dt>
                        <dd class="lk-order-sum__value"><?= number_format((float)$order['PRICE_DELIVERY'], 0, '.', ' ') ?> ₽</dd>
                    </div>
                    <?php endif; ?>
                    <div class="lk-order-sum__row lk-order-sum__row--total">
                        <dt class="lk-order-sum__label">Итого</dt>
                        <dd class="lk-order-sum__value lk-order-sum__value--total"><?= number_format((float)$order['PRICE'], 0, '.', ' ') ?> ₽</dd>
                    </div>
                </dl>
            </div>

            <!-- Доставка -->
            <?php if (!empty($shipments)): ?>
            <div class="lk-order-section">
                <h2 class="lk-order-section__title">Доставка</h2>
                <?php foreach ($shipments as $shipment):
                    if ($shipment['SYSTEM'] === 'Y') continue;
                ?>
                <dl class="lk-order-info">
                    <div class="lk-order-info__row">
                        <dt class="lk-order-info__label">Служба</dt>
                        <dd class="lk-order-info__value"><?= htmlspecialcharsEx($shipment['DELIVERY_NAME'] ?? '') ?></dd>
                    </div>
                    <?php if (!empty($shipment['TRACKING_NUMBER'])): ?>
                    <div class="lk-order-info__row">
                        <dt class="lk-order-info__label">Трек</dt>
                        <dd class="lk-order-info__value">
                            <span class="lk-order-info__track"><?= htmlspecialcharsEx($shipment['TRACKING_NUMBER']) ?></span>
                        </dd>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($shipment['PROPS']['ADDRESS']['VALUE'])): ?>
                    <div class="lk-order-info__row">
                        <dt class="lk-order-info__label">Адрес</dt>
                        <dd class="lk-order-info__value"><?= htmlspecialcharsEx($shipment['PROPS']['ADDRESS']['VALUE']) ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Оплата -->
            <?php if (!empty($payments)): ?>
            <div class="lk-order-section">
                <h2 class="lk-order-section__title">Оплата</h2>
                <?php foreach ($payments as $payment): ?>
                <dl class="lk-order-info">
                    <div class="lk-order-info__row">
                        <dt class="lk-order-info__label">Способ</dt>
                        <dd class="lk-order-info__value"><?= htmlspecialcharsEx($payment['PAY_SYSTEM_NAME'] ?? '') ?></dd>
                    </div>
                    <div class="lk-order-info__row">
                        <dt class="lk-order-info__label">Статус</dt>
                        <dd class="lk-order-info__value"><?= $payment['PAID'] === 'Y' ? 'Оплачено' : 'Ожидает оплаты' ?></dd>
                    </div>
                </dl>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="lk-order-detail__back">
                <a href="/personal/order/list/" class="btn btn--ghost">← Вернуться к заказам</a>
            </div>

        </aside>

    </div><!-- /lk-order-detail__grid -->

</div><!-- /lk-order-detail -->
