<?php
/**
 * Шаблон компонента main.profile
 * Путь: /local/templates/chokerz/components/bitrix/main.profile/.default/template.php
 * Профиль пользователя: личные данные, адрес, безопасность, Telegram-уведомления
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */
$fields     = $arResult['FIELDS']   ?? [];
$errors     = $arResult['ERRORS']   ?? [];
$success    = $arResult['SUCCESS']  ?? false;

// Текущие значения полей из $arResult
$getValue = static function (string $key) use ($arResult): string {
    return htmlspecialcharsEx($arResult[$key] ?? '');
};
?>

<div class="lk-profile" data-profile-form>

    <div class="lk-page-head">
        <h1 class="lk-page-head__title">Профиль и настройки</h1>
    </div>

    <?php if ($success): ?>
    <div class="lk-profile__success lk-form-message lk-form-message--success" role="alert">
        Данные успешно сохранены.
    </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    <div class="lk-profile__errors lk-form-message lk-form-message--error" role="alert">
        <?php foreach ($errors as $err): ?>
        <p class="lk-form-message__text"><?= htmlspecialcharsEx($err['TEXT'] ?? $err) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form
        class="lk-profile__form"
        action="<?= $arResult['FORM_ACTION'] ?? '' ?>"
        method="post"
        enctype="multipart/form-data"
        novalidate
        data-ajax-form="profile"
    >

        <?= bitrix_sessid_post() ?>

        <!-- ========================
             ЛИЧНЫЕ ДАННЫЕ
        ======================== -->
        <fieldset class="lk-profile__section lk-form-section" id="personal-data">
            <legend class="lk-form-section__title">Личные данные</legend>

            <div class="lk-form-section__grid">

                <div class="lk-form-field <?= !empty($arResult['ERRORS']['NAME']) ? 'lk-form-field--error' : '' ?>">
                    <label for="profile-name" class="lk-form-field__label">Имя</label>
                    <input
                        type="text"
                        id="profile-name"
                        name="NAME"
                        class="lk-form-field__input"
                        value="<?= $getValue('NAME') ?>"
                        autocomplete="given-name"
                        placeholder="Введите имя"
                    >
                    <?php if (!empty($arResult['ERRORS']['NAME'])): ?>
                    <span class="lk-form-field__error"><?= htmlspecialcharsEx($arResult['ERRORS']['NAME']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="lk-form-field">
                    <label for="profile-last-name" class="lk-form-field__label">Фамилия</label>
                    <input
                        type="text"
                        id="profile-last-name"
                        name="LAST_NAME"
                        class="lk-form-field__input"
                        value="<?= $getValue('LAST_NAME') ?>"
                        autocomplete="family-name"
                        placeholder="Введите фамилию"
                    >
                </div>

                <div class="lk-form-field">
                    <label for="profile-second-name" class="lk-form-field__label">Отчество</label>
                    <input
                        type="text"
                        id="profile-second-name"
                        name="SECOND_NAME"
                        class="lk-form-field__input"
                        value="<?= $getValue('SECOND_NAME') ?>"
                        autocomplete="additional-name"
                        placeholder="Введите отчество"
                    >
                </div>

                <div class="lk-form-field <?= !empty($arResult['ERRORS']['EMAIL']) ? 'lk-form-field--error' : '' ?>">
                    <label for="profile-email" class="lk-form-field__label">E-mail</label>
                    <input
                        type="email"
                        id="profile-email"
                        name="EMAIL"
                        class="lk-form-field__input"
                        value="<?= $getValue('EMAIL') ?>"
                        autocomplete="email"
                        placeholder="email@example.com"
                    >
                    <?php if (!empty($arResult['ERRORS']['EMAIL'])): ?>
                    <span class="lk-form-field__error"><?= htmlspecialcharsEx($arResult['ERRORS']['EMAIL']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="lk-form-field">
                    <label for="profile-phone" class="lk-form-field__label">Телефон</label>
                    <input
                        type="tel"
                        id="profile-phone"
                        name="PERSONAL_PHONE"
                        class="lk-form-field__input"
                        value="<?= $getValue('PERSONAL_PHONE') ?>"
                        autocomplete="tel"
                        placeholder="+7 (___) ___-__-__"
                    >
                </div>

                <div class="lk-form-field">
                    <label for="profile-birthday" class="lk-form-field__label">Дата рождения</label>
                    <input
                        type="date"
                        id="profile-birthday"
                        name="PERSONAL_BIRTHDAY"
                        class="lk-form-field__input"
                        value="<?= $getValue('PERSONAL_BIRTHDAY') ?>"
                        autocomplete="bday"
                    >
                </div>

            </div><!-- /lk-form-section__grid -->

            <div class="lk-form-section__actions">
                <button type="submit" name="save_personal" class="btn btn--primary" data-submit-personal>
                    Сохранить данные
                </button>
            </div>

        </fieldset>

        <!-- ========================
             АДРЕСА ДОСТАВКИ
        ======================== -->
        <fieldset class="lk-profile__section lk-form-section" id="addresses">
            <legend class="lk-form-section__title">Адреса для доставки</legend>

            <?php
            // Сохранённые профили адресов из CSaleOrderUserProps
            $addressProfiles = [];
            $res = CSaleOrderUserProps::GetList(
                ['ID' => 'DESC'],
                ['USER_ID' => $GLOBALS['USER']->GetID()],
                false,
                ['nTopCount' => 10]
            );
            while ($row = $res->Fetch()) {
                $addressProfiles[] = $row;
            }
            ?>

            <?php if (!empty($addressProfiles)): ?>
            <ul class="lk-address-list" data-address-list>
                <?php foreach ($addressProfiles as $profile): ?>
                <li class="lk-address-list__item lk-address-item" data-address-id="<?= (int)$profile['ID'] ?>">
                    <span class="lk-address-item__name"><?= htmlspecialcharsEx($profile['NAME']) ?></span>
                    <button
                        type="button"
                        class="lk-address-item__delete btn btn--ghost-small"
                        data-delete-address="<?= (int)$profile['ID'] ?>"
                        aria-label="Удалить адрес"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            <path d="M10 11v6M14 11v6"/>
                        </svg>
                    </button>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <div class="lk-address-add" data-address-add>
                <div class="lk-form-field">
                    <label for="address-profile-name" class="lk-form-field__label">Название профиля (для сохранения)</label>
                    <input
                        type="text"
                        id="address-profile-name"
                        name="address_profile_name"
                        class="lk-form-field__input"
                        placeholder="Например: Дом, Работа"
                    >
                </div>
                <div class="lk-form-field">
                    <label for="address-full" class="lk-form-field__label">Адрес для доставки</label>
                    <input
                        type="text"
                        id="address-full"
                        name="address_full"
                        class="lk-form-field__input"
                        placeholder="Город, улица, дом, квартира"
                        autocomplete="street-address"
                    >
                </div>
                <button type="button" class="btn btn--outline lk-address-add__btn" data-save-address>
                    Сохранить адрес
                </button>
            </div>

        </fieldset>

        <!-- ========================
             УВЕДОМЛЕНИЯ
        ======================== -->
        <fieldset class="lk-profile__section lk-form-section" id="notifications">
            <legend class="lk-form-section__title">Уведомления</legend>

            <div class="lk-notifications">

                <div class="lk-notifications__sub lk-notifications-sub">
                    <div class="lk-notifications-sub__info">
                        <span class="lk-notifications-sub__title">Статусы доставки</span>
                        <span class="lk-notifications-sub__desc">Получать уведомления при смене статуса заказа</span>
                    </div>
                    <label class="lk-toggle" aria-label="Уведомления о доставке">
                        <input type="checkbox" name="notify_delivery" class="lk-toggle__input" value="Y"
                            <?= !empty($arResult['NOTIFY_DELIVERY']) ? 'checked' : '' ?>>
                        <span class="lk-toggle__track" aria-hidden="true"></span>
                    </label>
                </div>

                <div class="lk-notifications__sub lk-notifications-sub">
                    <div class="lk-notifications-sub__info">
                        <span class="lk-notifications-sub__title">Новые акции</span>
                        <span class="lk-notifications-sub__desc">Персональные предложения и скидки</span>
                    </div>
                    <label class="lk-toggle" aria-label="Уведомления о акциях">
                        <input type="checkbox" name="notify_promo" class="lk-toggle__input" value="Y"
                            <?= !empty($arResult['NOTIFY_PROMO']) ? 'checked' : '' ?>>
                        <span class="lk-toggle__track" aria-hidden="true"></span>
                    </label>
                </div>

                <!-- Telegram -->
                <div class="lk-notifications__sub lk-notifications-sub lk-notifications-sub--tg">
                    <div class="lk-notifications-sub__info">
                        <span class="lk-notifications-sub__title">Telegram-уведомления</span>
                        <span class="lk-notifications-sub__desc" data-tg-status>
                            <?php
                            // Статус привязки Telegram берём из UF-поля пользователя
                            $tgChatId = \Bitrix\Main\UserTable::getList([
                                'filter' => ['=ID' => $GLOBALS['USER']->GetID()],
                                'select' => ['UF_TG_CHAT_ID'],
                                'limit'  => 1,
                            ])->fetchAll()[0]['UF_TG_CHAT_ID'] ?? null;
                            ?>
                            <?php if ($tgChatId): ?>
                            Telegram подключён
                            <?php else: ?>
                            Telegram не подключён
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($tgChatId): ?>
                    <button type="button" class="btn btn--ghost-small" data-tg-disconnect>Отключить</button>
                    <?php else: ?>
                    <a
                        href="/personal/telegram-connect/?uid=<?= (int)$GLOBALS['USER']->GetID() ?>"
                        class="btn btn--tg"
                        target="_blank"
                        rel="noopener noreferrer"
                    >Подключить</a>
                    <?php endif; ?>
                </div>

            </div>

        </fieldset>

        <!-- ========================
             БЕЗОПАСНОСТЬ
        ======================== -->
        <fieldset class="lk-profile__section lk-form-section" id="security">
            <legend class="lk-form-section__title">Безопасность</legend>

            <div class="lk-form-section__grid">

                <div class="lk-form-field">
                    <label for="profile-pass" class="lk-form-field__label">Новый пароль</label>
                    <input
                        type="password"
                        id="profile-pass"
                        name="PASSWORD"
                        class="lk-form-field__input"
                        autocomplete="new-password"
                        placeholder="Введите новый пароль"
                    >
                </div>

                <div class="lk-form-field">
                    <label for="profile-pass-confirm" class="lk-form-field__label">Подтверждение пароля</label>
                    <input
                        type="password"
                        id="profile-pass-confirm"
                        name="CONFIRM_PASSWORD"
                        class="lk-form-field__input"
                        autocomplete="new-password"
                        placeholder="Повторите пароль"
                    >
                </div>

            </div>

            <p class="lk-form-section__hint">Заполните только если хотите изменить пароль.</p>

            <div class="lk-form-section__actions">
                <button type="submit" name="save_security" class="btn btn--outline">
                    Изменить пароль
                </button>
                <a href="/personal/logout/?sessid=<?= bitrix_sessid() ?>" class="btn btn--ghost lk-profile__logout-all" data-logout-all>
                    Выйти со всех устройств
                </a>
            </div>

        </fieldset>

    </form>

</div><!-- /lk-profile -->
