<?php
/**
 * Шаблон: sale.order.ajax/.default/template.php
 * Блок: checkout, order-form, delivery-options, payment-options, order-summary
 * Требования: BEM, Vanilla JS ES6+, без inline JS/CSS, Bitrix D7
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/** @var array $arResult */
/** @var array $arParams */
/** @var CBitrixComponentTemplate $this */

$bOrderConfirmed = ($arResult['SHOW_ORDER'] === 'Y');
?>
<?php if ($bOrderConfirmed): ?>
<!-- ============================
     СТРАНИЦА: ЗАКАЗ ОФОРМЛЕН
     ============================ -->
<section class="order-success">
    <div class="order-success__icon" aria-hidden="true">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="9 12 11 14 15 10"/>
        </svg>
    </div>
    <p class="order-success__eyebrow">Заказ оформлен</p>
    <h1 class="order-success__title">Спасибо за заказ в CHOKERZ</h1>
    <p class="order-success__subtitle">
        Мы отправили детали заказа на
        <?= htmlspecialchars($arResult['ORDER']['USER_EMAIL'] ?? '') ?>
        и в SMS на <?= htmlspecialchars($arResult['ORDER']['USER_PHONE'] ?? '') ?>.
    </p>

    <div class="order-success__details">
        <div class="order-success__detail-row">
            <span class="order-success__detail-label">Номер заказа</span>
            <span class="order-success__detail-value"><?= htmlspecialchars($arResult['ORDER']['ACCOUNT_NUMBER'] ?? '') ?></span>
        </div>
        <div class="order-success__detail-row">
            <span class="order-success__detail-label">Дата оформления</span>
            <span class="order-success__detail-value"><?= htmlspecialchars($arResult['ORDER']['DATE_INSERT'] ?? '') ?></span>
        </div>
        <div class="order-success__detail-row">
            <span class="order-success__detail-label">Состав заказа</span>
            <?php
            $iItemCount   = (int)count($arResult['ORDER']['BASKET_ITEMS'] ?? []);
            $arItemForms  = ['товар', 'товара', 'товаров'];
            $iMod10       = $iItemCount % 10;
            $iMod100      = $iItemCount % 100;
            if ($iMod10 === 1 && $iMod100 !== 11) {
                $sItemWord = $arItemForms[0];
            } elseif ($iMod10 >= 2 && $iMod10 <= 4 && ($iMod100 < 10 || $iMod100 >= 20)) {
                $sItemWord = $arItemForms[1];
            } else {
                $sItemWord = $arItemForms[2];
            }
            ?>
            <span class="order-success__detail-value"><?= $iItemCount ?> <?= $sItemWord ?></span>
        </div>
        <div class="order-success__detail-row">
            <span class="order-success__detail-label">Итого к оплате</span>
            <span class="order-success__detail-value"><?= htmlspecialchars((string)($arResult['ORDER']['PRICE'] ?? '')) ?> ₽</span>
        </div>
    </div>

    <!-- Детали: адрес, доставка, оплата, статус -->
    <div class="order-success__grid">
        <div class="order-success__block">
            <p class="order-success__block-title">Адрес доставки</p>
            <p class="order-success__block-text"><?= htmlspecialchars($arResult['ORDER']['DELIVERY_ADDRESS'] ?? '') ?></p>
            <p class="order-success__block-text">
                Получатель: <?= htmlspecialchars($arResult['ORDER']['USER_NAME'] ?? '') ?> &nbsp;
                Телефон: <?= htmlspecialchars($arResult['ORDER']['USER_PHONE'] ?? '') ?>
            </p>
        </div>
        <div class="order-success__block">
            <p class="order-success__block-title">Способ доставки</p>
            <p class="order-success__block-text"><?= htmlspecialchars($arResult['ORDER']['DELIVERY_NAME'] ?? '') ?></p>
        </div>
        <div class="order-success__block">
            <p class="order-success__block-title">Оплата</p>
            <p class="order-success__block-text"><?= htmlspecialchars($arResult['ORDER']['PAY_SYSTEM_NAME'] ?? '') ?></p>
        </div>
        <div class="order-success__block">
            <p class="order-success__block-title">Статус заказа</p>
            <ul class="order-success__status-list">
                <li>Заказ принят в работу</li>
                <li>Подтверждение наличия амуниции на складе</li>
                <li>Подготовка и передача в службу доставки</li>
            </ul>
        </div>
    </div>

    <?php if (!empty($arResult['ORDER']['BASKET_ITEMS'])): ?>
    <div class="order-success__items">
        <p class="order-success__block-title">Товары в заказе</p>
        <?php foreach ($arResult['ORDER']['BASKET_ITEMS'] as $arItem): ?>
        <div class="order-success__item-row">
            <div class="order-success__item-info">
                <span class="order-success__item-name"><?= htmlspecialchars($arItem['NAME']) ?></span>
                <?php if (!empty($arItem['PROPS'])): ?>
                <span class="order-success__item-props">
                    <?php foreach ($arItem['PROPS'] as $arProp): ?>
                        <?= htmlspecialchars($arProp['NAME']) ?>: <?= htmlspecialchars($arProp['VALUE']) ?>&nbsp;
                    <?php endforeach; ?>
                </span>
                <?php endif; ?>
            </div>
            <div class="order-success__item-price">
                <?= (int)$arItem['QUANTITY'] ?> шт.
                &times; <?= htmlspecialchars((string)($arItem['PRICE'] ?? '')) ?> ₽
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="order-success__actions">
        <a href="/personal/orders/" class="btn btn--primary">Перейти в личный кабинет</a>
        <a href="/catalog/" class="btn btn--ghost">Вернуться в каталог</a>
    </div>
</section>

<?php else: ?>
<!-- ============================
     ФОРМА ОФОРМЛЕНИЯ ЗАКАЗА
     ============================ -->
<section class="checkout">
    <div class="container">

        <!-- Хлебные крошки -->
        <?php if (!empty($arResult['BREADCRUMBS'])): ?>
        <nav class="breadcrumbs checkout__breadcrumbs" aria-label="Хлебные крошки">
            <?php foreach ($arResult['BREADCRUMBS'] as $arCrumb): ?>
                <?php if (!empty($arCrumb['LINK'])): ?>
                    <a class="breadcrumbs__link" href="<?= htmlspecialchars($arCrumb['LINK']) ?>"><?= htmlspecialchars($arCrumb['TITLE']) ?></a>
                    <span class="breadcrumbs__sep" aria-hidden="true">/</span>
                <?php else: ?>
                    <span class="breadcrumbs__current"><?= htmlspecialchars($arCrumb['TITLE']) ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <p class="checkout__eyebrow">Оформление</p>
        <h1 class="checkout__title">Оформление заказа</h1>
        <p class="checkout__subtitle">Укажите контакты, выберите адрес доставки и способ оплаты. Мы подтвердим заказ и отправим амуницию в максимально короткие сроки.</p>

        <!-- Глобальные ошибки -->
        <?php if (!empty($arResult['ERROR_MESSAGE'])): ?>
        <div class="checkout__error-banner" role="alert">
            <?= htmlspecialchars($arResult['ERROR_MESSAGE']) ?>
        </div>
        <?php endif; ?>

        <div class="checkout__container" id="js-checkout-container">

            <!-- ====================
                 ЛЕВАЯ КОЛОНКА: ФОРМА
                 ==================== -->
            <div class="checkout__main">

                <?php $sFormAction = $arResult['FORM_ACTION'] ?? '/checkout/'; ?>
                <form
                    id="js-checkout-form"
                    class="checkout__form"
                    action="<?= htmlspecialchars($sFormAction) ?>"
                    method="post"
                    novalidate
                    data-checkout-form
                >
                    <?= bitrix_sessid_post() ?>
                    <input type="hidden" name="action" value="saveOrderAjax">

                    <!-- ==================
                         СЕКЦИЯ: КОНТАКТЫ
                         ================== -->
                    <div class="order-form" data-section="contacts">
                        <p class="checkout__section-label">Контакты</p>
                        <div class="order-form__section">
                            <p class="order-form__section-title">Контакты</p>
                            <p class="order-form__section-desc">Используется для уведомлений о статусе заказа и доставке.</p>

                            <?php
                            $arContactProps = [];
                            if (!empty($arResult['ORDER_PROP']['properties'])) {
                                foreach ($arResult['ORDER_PROP']['properties'] as $arProp) {
                                    $arContactProps[$arProp['CODE']] = $arProp;
                                }
                            }
                            ?>

                            <div class="order-form__grid">

                                <!-- Имя -->
                                <div class="order-form__field">
                                    <label class="order-form__label order-form__label--required" for="checkout-name">Имя</label>
                                    <input
                                        type="text"
                                        id="checkout-name"
                                        name="ORDER_PROP[<?= (int)($arContactProps['NAME']['ID'] ?? 0) ?>]"
                                        class="order-form__input <?= !empty($arContactProps['NAME']['ERROR']) ? 'order-form__input--error' : '' ?>"
                                        placeholder="Как к вам обращаться"
                                        value="<?= htmlspecialchars($arContactProps['NAME']['VALUE'] ?? '') ?>"
                                        autocomplete="given-name"
                                        data-validate="required"
                                        data-validate-msg="Укажите имя"
                                    >
                                    <?php if (!empty($arContactProps['NAME']['ERROR'])): ?>
                                    <span class="checkout__error" role="alert"><?= htmlspecialchars($arContactProps['NAME']['ERROR']) ?></span>
                                    <?php else: ?>
                                    <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                                    <?php endif; ?>
                                </div>

                                <!-- Фамилия -->
                                <div class="order-form__field">
                                    <label class="order-form__label" for="checkout-last-name">Фамилия <span class="order-form__label-optional">(необязательно)</span></label>
                                    <input
                                        type="text"
                                        id="checkout-last-name"
                                        name="ORDER_PROP[<?= (int)($arContactProps['LAST_NAME']['ID'] ?? 0) ?>]"
                                        class="order-form__input"
                                        placeholder="Можно оставить пустым"
                                        value="<?= htmlspecialchars($arContactProps['LAST_NAME']['VALUE'] ?? '') ?>"
                                        autocomplete="family-name"
                                    >
                                    <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                                </div>

                                <!-- Телефон -->
                                <div class="order-form__field">
                                    <label class="order-form__label order-form__label--required" for="checkout-phone">Телефон</label>
                                    <input
                                        type="tel"
                                        id="checkout-phone"
                                        name="ORDER_PROP[<?= (int)($arContactProps['PHONE']['ID'] ?? 0) ?>]"
                                        class="order-form__input <?= !empty($arContactProps['PHONE']['ERROR']) ? 'order-form__input--error' : '' ?>"
                                        placeholder="+7 (___) ___-__-__"
                                        value="<?= htmlspecialchars($arContactProps['PHONE']['VALUE'] ?? '') ?>"
                                        autocomplete="tel"
                                        data-validate="required|phone"
                                        data-validate-msg="Введите корректный номер телефона"
                                        inputmode="tel"
                                    >
                                    <?php if (!empty($arContactProps['PHONE']['ERROR'])): ?>
                                    <span class="checkout__error" role="alert"><?= htmlspecialchars($arContactProps['PHONE']['ERROR']) ?></span>
                                    <?php else: ?>
                                    <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                                    <?php endif; ?>
                                </div>

                                <!-- Email -->
                                <div class="order-form__field">
                                    <label class="order-form__label order-form__label--required" for="checkout-email">Email</label>
                                    <input
                                        type="email"
                                        id="checkout-email"
                                        name="ORDER_PROP[<?= (int)($arContactProps['EMAIL']['ID'] ?? 0) ?>]"
                                        class="order-form__input <?= !empty($arContactProps['EMAIL']['ERROR']) ? 'order-form__input--error' : '' ?>"
                                        placeholder="example@mail.ru"
                                        value="<?= htmlspecialchars($arContactProps['EMAIL']['VALUE'] ?? '') ?>"
                                        autocomplete="email"
                                        data-validate="required|email"
                                        data-validate-msg="Введите корректный email"
                                        inputmode="email"
                                    >
                                    <?php if (!empty($arContactProps['EMAIL']['ERROR'])): ?>
                                    <span class="checkout__error" role="alert"><?= htmlspecialchars($arContactProps['EMAIL']['ERROR']) ?></span>
                                    <?php else: ?>
                                    <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                                    <?php endif; ?>
                                </div>

                            </div><!-- /.order-form__grid -->

                            <?php if ($arResult['USER_REGISTERED'] === 'Y'): ?>
                            <p class="order-form__autofill-note">
                                Если вы уже оформляли заказ ранее, система может автоматически подставить данные после входа в личный кабинет.
                            </p>
                            <?php endif; ?>

                        </div><!-- /.order-form__section -->
                    </div><!-- /.order-form (contacts) -->

                    <!-- =====================
                         СЕКЦИЯ: АДРЕС ДОСТАВКИ
                         ===================== -->
                    <div class="order-form" data-section="delivery-address">
                        <p class="checkout__section-label">Адрес доставки</p>
                        <div class="order-form__section">
                            <div class="order-form__section-head">
                                <p class="order-form__section-title">Адрес доставки</p>
                                <div class="order-form__section-actions">
                                    <?php if (!empty($arResult['USER_ADDRESSES'])): ?>
                                    <button
                                        type="button"
                                        class="btn btn--ghost btn--sm"
                                        data-address-saved-toggle
                                    >Выбрать из сохранённых</button>
                                    <?php endif; ?>
                                    <button
                                        type="button"
                                        class="btn btn--link btn--sm"
                                        data-address-new-toggle
                                    >Новый адрес</button>
                                </div>
                            </div>
                            <p class="order-form__section-desc">Выберите сохранённый адрес или добавьте новый.</p>

                            <!-- Сохранённые адреса (если есть) -->
                            <?php if (!empty($arResult['USER_ADDRESSES'])): ?>
                            <div class="delivery-address" id="js-saved-addresses" data-saved-addresses>
                                <?php foreach ($arResult['USER_ADDRESSES'] as $iAddrIdx => $arAddr): ?>
                                <div class="delivery-address__item <?= $iAddrIdx === 0 ? 'delivery-address__item--selected' : '' ?>" data-address-id="<?= (int)$arAddr['ID'] ?>">
                                    <div class="delivery-address__item-head">
                                        <span class="delivery-address__item-name"><?= htmlspecialchars($arAddr['TITLE'] ?? 'Адрес ' . ($iAddrIdx + 1)) ?></span>
                                        <?php if ($iAddrIdx === 0): ?>
                                        <span class="delivery-address__badge">По умолчанию</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="delivery-address__item-text">
                                        <?= htmlspecialchars($arAddr['CITY'] ?? '') ?>, <?= htmlspecialchars($arAddr['STREET'] ?? '') ?>
                                    </p>
                                    <p class="delivery-address__item-meta">
                                        Получатель: <?= htmlspecialchars($arAddr['NAME'] ?? '') ?> &nbsp;
                                        Телефон: <?= htmlspecialchars($arAddr['PHONE'] ?? '') ?>
                                    </p>
                                    <div class="delivery-address__item-actions">
                                        <button type="button" class="btn btn--link btn--xs" data-address-edit="<?= (int)$arAddr['ID'] ?>">Изменить</button>
                                        <button type="button" class="btn btn--link btn--xs" data-address-add-new>Выбрать другой адрес</button>
                                    </div>
                                    <input type="hidden" name="ADDRESS_ID" value="<?= (int)$arAddr['ID'] ?>">
                                </div>
                                <?php endforeach; ?>
                                <p class="order-form__note">При выборе другого адреса стоимости и варианты доставки могут изменяться.</p>
                            </div>
                            <?php endif; ?>

                        </div><!-- /.order-form__section -->
                    </div><!-- /.order-form (delivery-address) -->


                    <!-- ======================
                         СЕКЦИЯ: СПОСОБ ДОСТАВКИ
                         ====================== -->
                    <?php if (!empty($arResult['DELIVERY_LIST'])): ?>
                    <div class="order-form" data-section="delivery">
                        <p class="checkout__section-label">Способ доставки</p>
                        <div class="order-form__section">
                            <p class="order-form__section-title">Способ доставки</p>
                            <p class="order-form__section-desc">Стоимость и сроки зависят от выбранного сервиса и вашего региона.</p>

                            <div class="delivery-options" role="radiogroup" aria-label="Способ доставки">
                                <?php
                                /*
                                 * Карта CODE службы доставки → тип иконки.
                                 * CODE задаётся в настройках службы доставки Битрикс.
                                 * Стандартные модули: cdek/sdek — СДЭК, russianpost/pochta — Почта России, ozon — OZON.
                                 */
                                $aDeliveryIconMap = [
                                    'cdek'        => 'layers',
                                    'sdek'        => 'layers',
                                    'russianpost' => 'mail',
                                    'pochta'      => 'mail',
                                    'ozon'        => 'globe',
                                    'ozon_rocket' => 'globe',
                                ];
                                ?>
                                <?php foreach ($arResult['DELIVERY_LIST'] as $arDelivery): ?>
                                <?php
                                $sDeliveryId   = (int)$arDelivery['ID'];
                                // CODE берём из поля CODE, не из NAME — п.6.2 ТЗ: без хардкода строк
                                $sDeliveryCode = mb_strtolower(trim($arDelivery['CODE'] ?? ''));
                                $sIconType     = $aDeliveryIconMap[$sDeliveryCode] ?? 'truck';
                                $bChecked      = ($arResult['DELIVERY_ID'] == $sDeliveryId);
                                // Флаг СДЭК для поля ПВЗ — по CODE
                                $bIsSdek       = in_array($sDeliveryCode, ['cdek', 'sdek'], true);
                                ?>
                                <label
                                    class="delivery-option <?= $bChecked ? 'delivery-option--checked' : '' ?>"
                                    data-delivery-option="<?= $sDeliveryId ?>"
                                >
                                    <input
                                        type="radio"
                                        name="DELIVERY_ID"
                                        value="<?= $sDeliveryId ?>"
                                        class="delivery-option__radio"
                                        <?= $bChecked ? 'checked' : '' ?>
                                        data-delivery-radio
                                    >
                                    <span class="delivery-option__icon" aria-hidden="true">
                                        <?php if ($sIconType === 'layers'): ?>
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                                        <?php elseif ($sIconType === 'mail'): ?>
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,4 12,13 2,4"/></svg>
                                        <?php elseif ($sIconType === 'globe'): ?>
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                        <?php else: // truck — дефолт ?>
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                                        <?php endif; ?>
                                    </span>
                                    <span class="delivery-option__content">
                                        <span class="delivery-option__name"><?= htmlspecialchars($arDelivery['NAME']) ?></span>
                                        <?php if (!empty($arDelivery['PERIOD_TEXT'])): ?>
                                        <span class="delivery-option__period"><?= htmlspecialchars($arDelivery['PERIOD_TEXT']) ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="delivery-option__price">
                                        <?php if (!empty($arDelivery['PRICE'])): ?>
                                            от <?= htmlspecialchars((string)$arDelivery['PRICE']) ?> ₽
                                        <?php else: ?>
                                            Будет рассчитана
                                        <?php endif; ?>
                                    </span>
                                </label>

                                <!-- Поле ПВЗ для СДЭК — по CODE, не по NAME -->
                                <?php if ($bIsSdek): ?>
                                <div
                                    class="delivery-pvz"
                                    id="js-sdek-pvz"
                                    data-delivery-extra="<?= $sDeliveryId ?>"
                                    <?= $bChecked ? '' : 'hidden' ?>
                                >
                                    <div class="order-form__field">
                                        <label class="order-form__label order-form__label--required" for="sdek-pvz">Пункт выдачи или адрес доставки СДЭК</label>
                                        <input
                                            type="text"
                                            id="sdek-pvz"
                                            name="ORDER_PROP[SDEK_PVZ]"
                                            class="order-form__input"
                                            placeholder="Адрес или код ПВЗ СДЭК"
                                            data-validate-conditional="required"
                                            data-validate-msg="Укажите пункт выдачи или адрес доставки СДЭК"
                                        >
                                        <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php endforeach; ?>
                            </div><!-- /.delivery-options -->

                            <p class="order-form__note">Точная стоимость доставки будет указана перед подтверждением заказа.</p>

                            <?php if (!empty($arResult['DELIVERY_ERROR'])): ?>
                            <span class="checkout__error" role="alert"><?= htmlspecialchars($arResult['DELIVERY_ERROR']) ?></span>
                            <?php endif; ?>

                        </div><!-- /.order-form__section -->
                    </div><!-- /.order-form (delivery) -->
                    <?php endif; ?>

                    <!-- ==================
                         СЕКЦИЯ: ОПЛАТА
                         ================== -->
                    <?php if (!empty($arResult['PAY_SYSTEM_LIST'])): ?>
                    <div class="order-form" data-section="payment">
                        <p class="checkout__section-label">Оплата</p>
                        <div class="order-form__section">
                            <p class="order-form__section-title">Оплата</p>
                            <p class="order-form__section-desc">Выберите удобный способ оплаты. Все платежи защищены.</p>

                            <div class="payment-options" role="radiogroup" aria-label="Способ оплаты">
                                <?php foreach ($arResult['PAY_SYSTEM_LIST'] as $arPaySystem): ?>
                                <?php
                                $iPayId        = (int)$arPaySystem['ID'];
                                $bPayChk       = ($arResult['PAY_SYSTEM_ID'] == $iPayId);
                                $sPayName      = $arPaySystem['NAME'] ?? '';
                                // CODE платёжной системы — по полю CODE, не по NAME. П.6.2 ТЗ.
                                // Стандартные: yookassa / yandex_money — ЮKassa, robokassa — Robokassa, cash — при получении.
                                $sPayCode      = mb_strtolower(trim($arPaySystem['CODE'] ?? ''));
                                $aPayIconMap   = [
                                    'yookassa'     => 'yookassa',
                                    'yandex_money' => 'yookassa',
                                    'yoo_money'    => 'yookassa',
                                    'robokassa'    => 'robokassa',
                                    'cash'         => 'cash',
                                    'cod'          => 'cash',
                                ];
                                $sPayIconType  = $aPayIconMap[$sPayCode] ?? 'card';
                                ?>
                                <label
                                    class="payment-option <?= $bPayChk ? 'payment-option--checked' : '' ?>"
                                    data-payment-option="<?= $iPayId ?>"
                                >
                                    <input
                                        type="radio"
                                        name="PAY_SYSTEM_ID"
                                        value="<?= $iPayId ?>"
                                        class="payment-option__radio"
                                        <?= $bPayChk ? 'checked' : '' ?>
                                        data-payment-radio
                                    >
                                    <span class="payment-option__icon" aria-hidden="true">
                                        <?php if ($sPayIconType === 'yookassa'): ?>
                                        <svg width="20" height="14" viewBox="0 0 40 28" fill="none"><rect width="40" height="28" rx="4" fill="#1A1A2E"/><text x="50%" y="19" font-size="10" fill="#fff" text-anchor="middle" font-family="Montserrat,sans-serif" font-weight="700">ЮKassa</text></svg>
                                        <?php elseif ($sPayIconType === 'robokassa'): ?>
                                        <svg width="20" height="14" viewBox="0 0 40 28" fill="none"><rect width="40" height="28" rx="4" fill="#E95D26"/><text x="50%" y="19" font-size="8" fill="#fff" text-anchor="middle" font-family="Montserrat,sans-serif" font-weight="700">Robokassa</text></svg>
                                        <?php elseif ($sPayIconType === 'cash'): ?>
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                        <?php else: // card — дефолт ?>
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                        <?php endif; ?>
                                    </span>
                                    <span class="payment-option__content">
                                        <span class="payment-option__name"><?= htmlspecialchars($sPayName) ?></span>
                                        <?php if (!empty($arPaySystem['DESCRIPTION'])): ?>
                                        <span class="payment-option__desc"><?= htmlspecialchars($arPaySystem['DESCRIPTION']) ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <?php if (!empty($arPaySystem['BONUS_TEXT'])): ?>
                                    <span class="payment-option__price"><?= htmlspecialchars($arPaySystem['BONUS_TEXT']) ?></span>
                                    <?php endif; ?>
                                </label>
                                <?php endforeach; ?>
                            </div><!-- /.payment-options -->

                            <?php if (!empty($arResult['PAY_SYSTEM_ERROR'])): ?>
                            <span class="checkout__error" role="alert"><?= htmlspecialchars($arResult['PAY_SYSTEM_ERROR']) ?></span>
                            <?php else: ?>
                            <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                            <?php endif; ?>

                            <p class="order-form__note">Данные карты вводятся на стороне платёжного сервиса. Мы не храим реквизиты карты.</p>

                        </div><!-- /.order-form__section -->
                    </div><!-- /.order-form (payment) -->
                    <?php endif; ?>

                    <!-- ==================
                         СЕКЦИЯ: КОММЕНТАРИЙ
                         ================== -->
                    <div class="order-form" data-section="comment">
                        <div class="order-form__section">
                            <p class="order-form__section-title">Комментарий к заказу</p>
                            <div class="order-form__field">
                                <label class="order-form__label" for="checkout-comment">Комментарий <span class="order-form__label-optional">(необязательно)</span></label>
                                <textarea
                                    id="checkout-comment"
                                    name="ORDER_DESCRIPTION"
                                    class="order-form__textarea"
                                    placeholder="Пожелания по доставке, упаковке или другое"
                                    rows="3"
                                ><?= htmlspecialchars($arResult['ORDER_DESCRIPTION'] ?? '') ?></textarea>
                                <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                            </div>
                        </div>
                    </div>

                </form><!-- /#js-checkout-form -->

            <!-- ====================
                 ФОРМА НОВОГО АДРЕСА
                 Вынесена за пределы #js-checkout-form.
                 role="dialog" внутри form — некорректная семантика (нарушение ARIA).
                 Поля адреса отправляются отдельным AJAX-запросом через data-address-save.
                 ==================== -->
                <div
                    class="address-form"
                    id="js-address-form"
                    data-address-form
                    hidden
                    aria-hidden="true"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="js-address-form-title"
                >
                    <div class="address-form__inner">
                        <p class="address-form__title" id="js-address-form-title">Адрес доставки</p>
                        <p class="address-form__desc">Заполните поля, чтобы сохранить адрес и использовать его при оформлении заказа.</p>

                        <div class="order-form__grid">

                            <div class="order-form__field order-form__field--wide">
                                <label class="order-form__label" for="addr-title">Название адреса</label>
                                <input
                                    type="text"
                                    id="addr-title"
                                    name="NEW_ADDRESS[TITLE]"
                                    class="order-form__input"
                                    placeholder="Например: Дом, Работа, Дача"
                                >
                                <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                            </div>

                            <div class="order-form__field">
                                <label class="order-form__label order-form__label--required" for="addr-fio">ФИО получателя</label>
                                <input
                                    type="text"
                                    id="addr-fio"
                                    name="NEW_ADDRESS[FIO]"
                                    class="order-form__input"
                                    placeholder="Как в документе или на посылке"
                                    data-validate="required"
                                    data-validate-msg="Укажите ФИО получателя"
                                    autocomplete="name"
                                >
                                <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                            </div>

                            <div class="order-form__field">
                                <label class="order-form__label order-form__label--required" for="addr-phone">Телефон получателя</label>
                                <input
                                    type="tel"
                                    id="addr-phone"
                                    name="NEW_ADDRESS[PHONE]"
                                    class="order-form__input"
                                    placeholder="+7 (___) ___-__-__"
                                    data-validate="required|phone"
                                    data-validate-msg="Введите корректный телефон"
                                    autocomplete="tel"
                                    inputmode="tel"
                                >
                                <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                            </div>

                            <div class="order-form__field">
                                <label class="order-form__label order-form__label--required" for="addr-region">Регион / область</label>
                                <input
                                    type="text"
                                    id="addr-region"
                                    name="NEW_ADDRESS[REGION]"
                                    class="order-form__input"
                                    placeholder="Например: Московская область"
                                    data-validate="required"
                                    data-validate-msg="Укажите регион"
                                    autocomplete="address-level1"
                                >
                                <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                            </div>

                            <div class="order-form__field">
                                <label class="order-form__label order-form__label--required" for="addr-city">Город / населённый пункт</label>
                                <input
                                    type="text"
                                    id="addr-city"
                                    name="NEW_ADDRESS[CITY]"
                                    class="order-form__input"
                                    placeholder="Например: Москва"
                                    data-validate="required"
                                    data-validate-msg="Укажите город"
                                    autocomplete="address-level2"
                                >
                                <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                            </div>

                            <div class="order-form__field order-form__field--wide">
                                <label class="order-form__label order-form__label--required" for="addr-street">Улица</label>
                                <input
                                    type="text"
                                    id="addr-street"
                                    name="NEW_ADDRESS[STREET]"
                                    class="order-form__input"
                                    placeholder="Улица, проспект и т.п."
                                    data-validate="required"
                                    data-validate-msg="Укажите улицу"
                                    autocomplete="address-line1"
                                >
                                <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                            </div>

                            <div class="order-form__field">
                                <label class="order-form__label order-form__label--required" for="addr-house">Дом</label>
                                <input
                                    type="text"
                                    id="addr-house"
                                    name="NEW_ADDRESS[HOUSE]"
                                    class="order-form__input"
                                    placeholder="№ дома / строение"
                                    data-validate="required"
                                    data-validate-msg="Укажите дом"
                                >
                                <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                            </div>

                            <div class="order-form__field">
                                <label class="order-form__label" for="addr-flat">Квартира / офис</label>
                                <input
                                    type="text"
                                    id="addr-flat"
                                    name="NEW_ADDRESS[FLAT]"
                                    class="order-form__input"
                                    placeholder="Кв., офис (если есть)"
                                    autocomplete="address-line2"
                                >
                                <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                            </div>

                            <div class="order-form__field">
                                <label class="order-form__label" for="addr-entrance">Подъезд / этаж</label>
                                <input
                                    type="text"
                                    id="addr-entrance"
                                    name="NEW_ADDRESS[ENTRANCE]"
                                    class="order-form__input"
                                    placeholder="По желанию"
                                >
                                <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                            </div>

                            <div class="order-form__field">
                                <label class="order-form__label" for="addr-zip">Индекс</label>
                                <input
                                    type="text"
                                    id="addr-zip"
                                    name="NEW_ADDRESS[ZIP]"
                                    class="order-form__input"
                                    placeholder="Почтовый индекс"
                                    inputmode="numeric"
                                    pattern="[0-9]{6}"
                                    autocomplete="postal-code"
                                >
                                <span class="checkout__error" role="alert" aria-live="polite" hidden></span>
                            </div>

                            <div class="order-form__field order-form__field--wide">
                                <label class="order-form__label" for="addr-comment">Комментарий для курьера <span class="order-form__label-optional">необязательно</span></label>
                                <input
                                    type="text"
                                    id="addr-comment"
                                    name="NEW_ADDRESS[COMMENT]"
                                    class="order-form__input"
                                    placeholder="Например: домофон не работает, вход со двора"
                                >
                                <p class="order-form__note">Этот текст увидит служба доставки. Не указывайте здесь конфиденциальные данные.</p>
                            </div>

                        </div><!-- /.order-form__grid -->

                        <div class="address-form__checkboxes">
                            <label class="checkout__checkbox">
                                <input type="checkbox" name="NEW_ADDRESS[IS_DEFAULT]" value="1" class="checkout__checkbox-input" data-addr-default>
                                <span class="checkout__checkbox-label">Сделать основным адресом по умолчанию</span>
                            </label>
                            <label class="checkout__checkbox">
                                <input type="checkbox" name="NEW_ADDRESS[USE_FOR_ALL]" value="1" class="checkout__checkbox-input">
                                <span class="checkout__checkbox-label">Использовать этот адрес для всех новых заказов</span>
                            </label>
                        </div>

                        <div class="address-form__actions">
                            <button type="button" class="btn btn--secondary" data-address-save>Сохранить адрес</button>
                            <button type="button" class="btn btn--ghost" data-address-cancel>Отмена</button>
                        </div>
                        <p class="address-form__footer-note">Вы всегда сможете изменить или удалить адрес в личном кабинете.</p>

                    </div><!-- /.address-form__inner -->
                </div><!-- /.address-form -->


            </div><!-- /.checkout__main -->

            <!-- ==========================
                 ПРАВАЯ КОЛОНКА: ИТОГО
                 ========================== -->
            <div class="checkout__aside">
                <aside class="order-summary" id="js-order-summary" aria-label="Итого по заказу">

                    <div class="order-summary__header">
                        <p class="order-summary__title">Итого</p>
                    </div>

                    <p class="order-summary__desc">Проверьте сумму заказа перед переходом к оформлению.</p>

                    <!-- Товары -->
                    <?php if (!empty($arResult['BASKET_ITEMS'])): ?>
                    <div class="order-summary__items">
                        <?php foreach ($arResult['BASKET_ITEMS'] as $arItem): ?>
                        <div class="order-summary__item">
                            <?php if (!empty($arItem['PREVIEW_PICTURE']['SRC'])): ?>
                            <img
                                class="order-summary__item-img"
                                src="<?= htmlspecialchars($arItem['PREVIEW_PICTURE']['SRC']) ?>"
                                alt="<?= htmlspecialchars($arItem['NAME']) ?>"
                                width="44"
                                height="44"
                                loading="lazy"
                            >
                            <?php else: ?>
                            <span class="order-summary__item-img order-summary__item-img--placeholder" aria-hidden="true"></span>
                            <?php endif; ?>
                            <span class="order-summary__item-name">
                                <?= htmlspecialchars($arItem['NAME']) ?>
                                <?php if ((int)$arItem['QUANTITY'] > 1): ?>
                                <span class="order-summary__item-qty">&times;<?= (int)$arItem['QUANTITY'] ?></span>
                                <?php endif; ?>
                            </span>
                            <span class="order-summary__item-price"><?= htmlspecialchars((string)($arItem['PRICE'] ?? '')) ?> ₽</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Строки итого -->
                    <div class="order-summary__totals">
                        <div class="order-summary__row">
                            <span class="order-summary__row-label">
                                Товары (<?= (int)$arResult['BASKET_PRICE']['QUANTITY'] ?>)
                            </span>
                            <span class="order-summary__row-value" id="js-summary-subtotal">
                                <?= htmlspecialchars((string)($arResult['BASKET_PRICE']['PRICE'] ?? '')) ?> ₽
                            </span>
                        </div>
                        <?php if (!empty($arResult['BASKET_PRICE']['DISCOUNT_PRICE']) && $arResult['BASKET_PRICE']['DISCOUNT_PRICE'] > 0): ?>
                        <div class="order-summary__row">
                            <span class="order-summary__row-label">Скидка</span>
                            <span class="order-summary__row-value order-summary__row-value--discount" id="js-summary-discount">
                                &minus; <?= htmlspecialchars((string)($arResult['BASKET_PRICE']['DISCOUNT_PRICE'] ?? '')) ?> ₽
                            </span>
                        </div>
                        <?php endif; ?>
                        <div class="order-summary__row">
                            <span class="order-summary__row-label">Доставка</span>
                            <span class="order-summary__row-value" id="js-summary-delivery">
                                <?= !empty($arResult['DELIVERY_PRICE']) ? htmlspecialchars((string)$arResult['DELIVERY_PRICE']) . ' ₽' : 'Будет рассчитана' ?>
                            </span>
                        </div>
                    </div><!-- /.order-summary__totals -->

                    <!-- Итого -->
                    <div class="order-summary__total-row">
                        <span class="order-summary__total-label">К оплате</span>
                        <span class="order-summary__total-value" id="js-summary-total">
                            <?= htmlspecialchars((string)($arResult['PRICE'] ?? $arResult['BASKET_PRICE']['PRICE'] ?? '')) ?> ₽
                        </span>
                    </div>

                    <p class="order-summary__note">
                        Итоговая сумма будет показана после расчёта доставки и перед подтверждением.
                    </p>

                    <!-- Соглашение -->
                    <div class="order-summary__agreements">
                        <label class="checkout__checkbox">
                            <input
                                type="checkbox"
                                name="AGREEMENT_POLICY"
                                value="1"
                                class="checkout__checkbox-input"
                                id="js-agreement-policy"
                                data-validate="required"
                                data-validate-msg="Необходимо принять условия"
                                form="js-checkout-form"
                            >
                            <span class="checkout__checkbox-label">
                                Я согласен с условиями
                                <a href="/policy/" target="_blank" rel="noopener noreferrer">Пользовательского соглашения</a>
                                и <a href="/privacy/" target="_blank" rel="noopener noreferrer">Политикой конфиденциальности</a>
                            </span>
                        </label>
                        <span class="checkout__error" role="alert" aria-live="polite" hidden></span>

                        <label class="checkout__checkbox">
                            <input
                                type="checkbox"
                                name="AGREEMENT_NEWSLETTER"
                                value="1"
                                class="checkout__checkbox-input"
                                form="js-checkout-form"
                            >
                            <span class="checkout__checkbox-label">
                                Хочу получать ранний доступ к новым коллекциям и акциям CHOKERZ.
                            </span>
                        </label>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="order-summary__actions">
                        <button
                            type="submit"
                            form="js-checkout-form"
                            class="btn btn--primary order-summary__submit"
                            id="js-checkout-submit"
                            data-checkout-submit
                        >
                            Подтвердить заказ
                        </button>
                        <a href="/cart/" class="btn btn--ghost order-summary__back">
                            Вернуться в корзину
                        </a>
                    </div>

                </aside>
            </div><!-- /.checkout__aside -->

        </div><!-- /.checkout__container -->
    </div><!-- /.container -->
</section>

<?php endif; // end order confirmed / form ?>
