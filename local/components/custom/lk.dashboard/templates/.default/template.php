<?php
/**
 * Шаблон дашборда личного кабинета CHOKERZ
 * Секции: приветствие, последний заказ, превью избранного, адреса, Telegram-уведомления
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */
$user      = $arResult['USER'];
$lastOrder = $arResult['LAST_ORDER'];
$wishlist  = $arResult['WISHLIST'];
$addresses = $arResult['ADDRESSES'];
$stats     = $arResult['STATS'];

$displayName = htmlspecialcharsEx($user['NAME'] ?? '');
?>

<div class="lk-dashboard">

    <!-- ========================
         ПРИВЕТСТВИЕ
    ======================== -->
    <div class="lk-dashboard__greeting lk-greeting">
        <div class="lk-greeting__content">
            <span class="lk-greeting__label">АКТИВНЫЙ КЛИЕНТ CHOKERZ</span>
            <h1 class="lk-greeting__name"><?= $displayName ?>, добрый день</h1>
            <p class="lk-greeting__subtitle">
                Всего заказов: <span class="lk-greeting__count"><?= (int)$stats['ORDERS_COUNT'] ?></span>
            </p>
        </div>
        <div class="lk-greeting__actions">
            <a href="/personal/order/list/" class="btn btn--outline lk-greeting__btn">Все заказы</a>
            <a href="/personal/wishlist/" class="btn btn--ghost lk-greeting__btn">Избранное</a>
        </div>
    </div>

    <!-- ========================
         ТЕКУЩИЙ ЗАКАЗ (ПОСЛЕДНИЙ)
    ======================== -->
    <?php if (!empty($lastOrder)): ?>
    <section class="lk-dashboard__section lk-last-order" aria-label="Последний заказ">

        <div class="lk-section-head">
            <span class="lk-section-head__label">ТЕКУЩИЙ ЗАКАЗ CHOKERZ</span>
            <a href="/personal/order/list/" class="lk-section-head__link">История заказов</a>
        </div>

        <div class="lk-last-order__card lk-order-card">

            <div class="lk-order-card__header">
                <div class="lk-order-card__meta">
                    <span class="lk-order-card__number">Заказ #<?= htmlspecialcharsEx($lastOrder['ACCOUNT_NUMBER']) ?></span>
                    <span class="lk-order-card__date"><?= htmlspecialcharsEx($lastOrder['DATE_FORMAT']) ?></span>
                </div>
                <div class="lk-order-card__status lk-order-status lk-order-status--<?= mb_strtolower($lastOrder['STATUS_ID']) ?>">
                    <?= htmlspecialcharsEx($lastOrder['STATUS_NAME']) ?>
                </div>
            </div>

            <?php if (!empty($lastOrder['ITEMS'])): ?>
            <ul class="lk-order-card__items lk-order-items" aria-label="Товары заказа">
                <?php foreach ($lastOrder['ITEMS'] as $item): ?>
                <li class="lk-order-items__item lk-order-item">
                    <span class="lk-order-item__name"><?= htmlspecialcharsEx($item['NAME']) ?></span>
                    <?php if (!empty($item['PROPS'])): ?>
                    <span class="lk-order-item__props">
                        <?php foreach ($item['PROPS'] as $prop): ?>
                        <?= htmlspecialcharsEx($prop['NAME']) ?>: <?= htmlspecialcharsEx($prop['VALUE']) ?>;
                        <?php endforeach; ?>
                    </span>
                    <?php endif; ?>
                    <span class="lk-order-item__qty"><?= (int)$item['QUANTITY'] ?> шт.</span>
                    <span class="lk-order-item__price"><?= number_format((float)$item['PRICE'], 0, '.', ' ') ?> ₽</span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <div class="lk-order-card__footer">
                <div class="lk-order-card__total">
                    Итого: <span class="lk-order-card__total-price"><?= number_format((float)$lastOrder['PRICE'], 0, '.', ' ') ?> ₽</span>
                </div>
                <?php if (!empty($lastOrder['DELIVERY_NAME'])): ?>
                <div class="lk-order-card__delivery"><?= htmlspecialcharsEx($lastOrder['DELIVERY_NAME']) ?></div>
                <?php endif; ?>
                <a href="/personal/order/detail/<?= (int)$lastOrder['ID'] ?>/" class="btn btn--primary lk-order-card__btn">
                    Подробнее
                </a>
            </div>

        </div>
    </section>
    <?php endif; ?>

    <!-- ========================
         ИЗБРАННОЕ И КОРЗИНА
    ======================== -->
    <?php if (!empty($wishlist)): ?>
    <section class="lk-dashboard__section lk-wishlist-preview" aria-label="Избранное">

        <div class="lk-section-head">
            <span class="lk-section-head__label">ИЗБРАННОЕ И КОРЗИНА</span>
            <a href="/personal/wishlist/" class="lk-section-head__link">Смотреть все</a>
        </div>

        <ul class="lk-wishlist-preview__list">
            <?php foreach ($wishlist as $product): ?>
            <li class="lk-wishlist-preview__item lk-wishlist-item">
                <a href="<?= htmlspecialcharsEx($product['DETAIL_PAGE_URL']) ?>" class="lk-wishlist-item__link">
                    <?php if (!empty($product['PREVIEW_SRC'])): ?>
                    <img
                        src="<?= htmlspecialcharsEx($product['PREVIEW_SRC']) ?>"
                        alt="<?= htmlspecialcharsEx($product['NAME']) ?>"
                        class="lk-wishlist-item__img"
                        loading="lazy"
                        width="80"
                        height="80"
                    >
                    <?php endif; ?>
                    <span class="lk-wishlist-item__name"><?= htmlspecialcharsEx($product['NAME']) ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

    </section>
    <?php endif; ?>

    <!-- ========================
         АДРЕСА ДОСТАВКИ
    ======================== -->
    <section class="lk-dashboard__section lk-addresses" aria-label="Адреса доставки">

        <div class="lk-section-head">
            <span class="lk-section-head__label">АДРЕСА ДОСТАВКИ</span>
            <a href="/personal/profile/#addresses" class="lk-section-head__link">Управление</a>
        </div>

        <?php if (!empty($addresses)): ?>
        <ul class="lk-addresses__list">
            <?php foreach ($addresses as $address): ?>
            <li class="lk-addresses__item lk-address-item">
                <span class="lk-address-item__name"><?= htmlspecialcharsEx($address['NAME']) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p class="lk-addresses__empty">Адреса не добавлены.</p>
        <a href="/personal/profile/#addresses" class="btn btn--outline lk-addresses__add-btn">Добавить адрес</a>
        <?php endif; ?>

    </section>

    <!-- ========================
         TELEGRAM-УВЕДОМЛЕНИЯ
    ======================== -->
    <section class="lk-dashboard__section lk-tg-notify" aria-label="Telegram-уведомления">

        <div class="lk-section-head">
            <span class="lk-section-head__label">ЛИЧНЫЕ УВЕДОМЛЕНИЯ</span>
        </div>

        <div class="lk-tg-notify__body">
            <div class="lk-tg-notify__info">
                <p class="lk-tg-notify__text">Подключите Telegram, чтобы получать уведомления о статусе заказа.</p>
            </div>
            <div class="lk-tg-notify__action" data-tg-connect>
                <?php
                // Telegram link генерируется в /local/php_interface/include/tg_notify.php
                // Передаём user_id для генерации deeplink
                global $USER;
                $tgLink = '/personal/telegram-connect/?uid=' . (int)$USER->GetID();
                ?>
                <a href="<?= $tgLink ?>" class="btn btn--tg lk-tg-notify__btn" target="_blank" rel="noopener noreferrer">
                    <svg class="lk-tg-notify__icon" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.162l-2.032 9.571c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L7.48 14.06l-2.95-.924c-.642-.204-.654-.642.135-.953l11.514-4.44c.535-.194 1.003.131.383.419z"/>
                    </svg>
                    Подключить Telegram
                </a>
            </div>
        </div>

    </section>

</div>
