<?php
/**
 * Шаблон: chokerz:modal.manager / .default / template.php
 * Содержит разметку всех модальных окон проекта CHOKERZ
 * BEM: modal, modal__overlay, modal__window, modal__close
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
/** @var array $arParams */

$isAuth = (bool)$arResult['AUTH']['IS_AUTHORIZED'];
?>

<!-- =========================================================
     MODAL OVERLAY — общий контейнер для всех модальных окон
     Управляется через data-modal-target / data-modal-open атрибуты
     ========================================================= -->
<div class="modal" id="modal-system" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal__overlay" data-modal-close tabindex="-1"></div>

    <!-- =======================================================
         MODAL: Личный кабинет — Авторизация / Регистрация
         Триггер: data-modal-open="auth"
         ======================================================= -->
    <div class="modal__window modal__window--auth" id="modal-auth" role="document" aria-labelledby="modal-auth-title" hidden>
        <button
            class="modal__close"
            type="button"
            data-modal-close
            aria-label="Закрыть"
        >
            <svg class="modal__close-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M1 1L15 15M15 1L1 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </button>

        <div class="modal-auth">
            <div class="modal-auth__header">
                <p class="modal-auth__subtitle">Личный кабинет</p>
                <?php if (!$isAuth): ?>
                <p class="modal-auth__description">Войдите или зарегистрируйтесь, чтобы управлять заказами, адресами и избранными товарами</p>
                <?php endif; ?>
            </div>

            <?php if (!$isAuth): ?>
            <!-- Таб-навигация: Вход / Регистрация -->
            <div class="modal-auth__tabs" role="tablist">
                <button
                    class="modal-auth__tab modal-auth__tab--active"
                    type="button"
                    role="tab"
                    aria-selected="true"
                    aria-controls="modal-auth-tab-login"
                    data-tab="login"
                    id="tab-login"
                >Вход</button>
                <button
                    class="modal-auth__tab"
                    type="button"
                    role="tab"
                    aria-selected="false"
                    aria-controls="modal-auth-tab-register"
                    data-tab="register"
                    id="tab-register"
                >Регистрация</button>
            </div>

            <!-- ---- ВХОД ---- -->
            <div
                class="modal-auth__panel modal-auth__panel--active"
                id="modal-auth-tab-login"
                role="tabpanel"
                aria-labelledby="tab-login"
            >
                <form
                    class="modal-auth__form"
                    id="form-auth-login"
                    method="post"
                    action="/local/ajax/auth/login.php"
                    novalidate
                    data-ajax-form
                    data-ajax-success="modal-auth-success"
                >
                    <?= bitrix_sessid_post() ?>

                    <div class="form-field">
                        <label class="form-field__label" for="auth-email">E-mail</label>
                        <input
                            class="form-field__input"
                            type="email"
                            id="auth-email"
                            name="USER_LOGIN"
                            autocomplete="email"
                            required
                            placeholder="example@mail.ru"
                        >
                        <span class="form-field__error" role="alert" aria-live="polite"></span>
                    </div>

                    <div class="form-field">
                        <label class="form-field__label" for="auth-password">Пароль</label>
                        <div class="form-field__password-wrap">
                            <input
                                class="form-field__input"
                                type="password"
                                id="auth-password"
                                name="USER_PASSWORD"
                                autocomplete="current-password"
                                required
                                placeholder="••••••••"
                            >
                            <button
                                class="form-field__eye"
                                type="button"
                                aria-label="Показать пароль"
                                data-password-toggle="auth-password"
                            >
                                <svg class="form-field__eye-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M10 4C5.5 4 1.7 6.9 0 11c1.7 4.1 5.5 7 10 7s8.3-2.9 10-7c-1.7-4.1-5.5-7-10-7Z" stroke="currentColor" stroke-width="1.4"/>
                                    <circle cx="10" cy="11" r="3" stroke="currentColor" stroke-width="1.4"/>
                                </svg>
                            </button>
                        </div>
                        <span class="form-field__error" role="alert" aria-live="polite"></span>
                    </div>

                    <div class="modal-auth__forgot">
                        <a class="modal-auth__forgot-link" href="/auth/?action=forgotpasswd" data-modal-close>Забыли пароль?</a>
                    </div>

                    <div class="modal-auth__form-footer">
                        <button class="btn btn--primary btn--full" type="submit">Войти</button>
                        <span class="modal-auth__form-error" role="alert" aria-live="assertive" hidden></span>
                    </div>
                </form>

                <!-- Социальные кнопки -->
                <?php if (!empty($arResult['SOCIAL_SERVICES'])): ?>
                <div class="modal-auth__social">
                    <p class="modal-auth__social-label">Войти через</p>
                    <div class="modal-auth__social-list">
                        <?php if (in_array('vk', $arResult['SOCIAL_SERVICES'], true)): ?>
                        <a
                            class="modal-auth__social-btn modal-auth__social-btn--vk"
                            href="/auth/?auth_service_id=vkontakte"
                            aria-label="Войти через ВКонтакте"
                        >
                            <svg class="modal-auth__social-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12.785 16.241s.288-.032.436-.194c.136-.148.131-.427.131-.427s-.019-1.304.587-1.496c.597-.189 1.364 1.26 2.176 1.816.614.421 1.08.329 1.08.329l2.17-.03s1.135-.07.597-1.195c-.044-.094-.314-.657-1.613-1.856-1.36-1.254-1.178-1.051.46-3.22.999-1.328 1.397-2.14 1.272-2.487-.12-.33-.855-.244-.855-.244l-2.44.015s-.181-.025-.315.056c-.132.08-.217.265-.217.265s-.386 1.03-.9 1.904c-1.086 1.842-1.52 1.94-1.697 1.825-.413-.266-.31-1.072-.31-1.644 0-1.788.271-2.533-.528-2.727-.265-.064-.46-.106-1.137-.113-.87-.009-1.605.003-2.022.207-.277.135-.49.437-.36.454.161.022.526.098.72.36.25.34.241 1.104.241 1.104s.144 2.104-.335 2.364c-.329.177-.78-.185-1.748-1.843-.497-.857-.872-1.805-.872-1.805s-.072-.178-.202-.274c-.157-.115-.376-.151-.376-.151l-2.32.015s-.349.01-.477.162c-.114.135-.009.414-.009.414s1.816 4.25 3.872 6.394c1.886 1.967 4.03 1.838 4.03 1.838h.97Z" fill="currentColor"/>
                            </svg>
                        </a>
                        <?php endif; ?>
                        <?php if (in_array('ok', $arResult['SOCIAL_SERVICES'], true)): ?>
                        <a
                            class="modal-auth__social-btn modal-auth__social-btn--ok"
                            href="/auth/?auth_service_id=odnoklassniki"
                            aria-label="Войти через Одноклассники"
                        >
                            <svg class="modal-auth__social-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm4.5 9.75a7.1 7.1 0 0 1-2.75.75l1.75 1.75a.75.75 0 1 1-1.06 1.06L12 18.06l-2.44 2.25a.75.75 0 1 1-1.06-1.06l1.75-1.75a7.1 7.1 0 0 1-2.75-.75.75.75 0 1 1 .75-1.3A5.5 5.5 0 0 0 12 17.5a5.5 5.5 0 0 0 3.75-1.05.75.75 0 1 1 .75 1.3Z" fill="currentColor"/>
                            </svg>
                        </a>
                        <?php endif; ?>
                        <?php if (in_array('tg', $arResult['SOCIAL_SERVICES'], true)): ?>
                        <a
                            class="modal-auth__social-btn modal-auth__social-btn--tg"
                            href="/local/ajax/auth/telegram.php"
                            aria-label="Войти через Telegram"
                        >
                            <svg class="modal-auth__social-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M9.036 15.572 8.704 20.1c.49 0 .702-.21.956-.461l2.294-2.196 4.754 3.48c.872.48 1.488.228 1.722-.802l3.122-14.6c.278-1.276-.464-1.774-1.314-1.46L1.986 10.208c-1.244.484-1.226 1.178-.212 1.49l4.572 1.42 10.618-6.666c.5-.33.956-.147.58.183L9.036 15.572Z" fill="currentColor"/>
                            </svg>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <!-- /ВХОД -->

            <!-- ---- РЕГИСТРАЦИЯ ---- -->
            <div
                class="modal-auth__panel"
                id="modal-auth-tab-register"
                role="tabpanel"
                aria-labelledby="tab-register"
                hidden
            >
                <form
                    class="modal-auth__form"
                    id="form-auth-register"
                    method="post"
                    action="/local/ajax/auth/register.php"
                    novalidate
                    data-ajax-form
                    data-ajax-success="modal-auth-success"
                >
                    <?= bitrix_sessid_post() ?>

                    <div class="form-field">
                        <label class="form-field__label" for="reg-name">Имя</label>
                        <input
                            class="form-field__input"
                            type="text"
                            id="reg-name"
                            name="NAME"
                            autocomplete="given-name"
                            required
                            placeholder="Ваше имя"
                        >
                        <span class="form-field__error" role="alert" aria-live="polite"></span>
                    </div>

                    <div class="form-field">
                        <label class="form-field__label" for="reg-email">E-mail</label>
                        <input
                            class="form-field__input"
                            type="email"
                            id="reg-email"
                            name="EMAIL"
                            autocomplete="email"
                            required
                            placeholder="example@mail.ru"
                        >
                        <span class="form-field__error" role="alert" aria-live="polite"></span>
                    </div>

                    <div class="form-field">
                        <label class="form-field__label" for="reg-phone">Телефон</label>
                        <input
                            class="form-field__input"
                            type="tel"
                            id="reg-phone"
                            name="PERSONAL_PHONE"
                            autocomplete="tel"
                            placeholder="+7 (___) ___-__-__"
                        >
                        <span class="form-field__error" role="alert" aria-live="polite"></span>
                    </div>

                    <div class="form-field">
                        <label class="form-field__label" for="reg-password">Пароль</label>
                        <div class="form-field__password-wrap">
                            <input
                                class="form-field__input"
                                type="password"
                                id="reg-password"
                                name="PASSWORD"
                                autocomplete="new-password"
                                required
                                placeholder="Минимум 8 символов"
                            >
                            <button
                                class="form-field__eye"
                                type="button"
                                aria-label="Показать пароль"
                                data-password-toggle="reg-password"
                            >
                                <svg class="form-field__eye-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M10 4C5.5 4 1.7 6.9 0 11c1.7 4.1 5.5 7 10 7s8.3-2.9 10-7c-1.7-4.1-5.5-7-10-7Z" stroke="currentColor" stroke-width="1.4"/>
                                    <circle cx="10" cy="11" r="3" stroke="currentColor" stroke-width="1.4"/>
                                </svg>
                            </button>
                        </div>
                        <span class="form-field__error" role="alert" aria-live="polite"></span>
                    </div>

                    <div class="form-field">
                        <label class="form-field__label" for="reg-password-confirm">Повторите пароль</label>
                        <div class="form-field__password-wrap">
                            <input
                                class="form-field__input"
                                type="password"
                                id="reg-password-confirm"
                                name="CONFIRM_PASSWORD"
                                autocomplete="new-password"
                                required
                                placeholder="••••••••"
                            >
                            <button
                                class="form-field__eye"
                                type="button"
                                aria-label="Показать пароль"
                                data-password-toggle="reg-password-confirm"
                            >
                                <svg class="form-field__eye-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M10 4C5.5 4 1.7 6.9 0 11c1.7 4.1 5.5 7 10 7s8.3-2.9 10-7c-1.7-4.1-5.5-7-10-7Z" stroke="currentColor" stroke-width="1.4"/>
                                    <circle cx="10" cy="11" r="3" stroke="currentColor" stroke-width="1.4"/>
                                </svg>
                            </button>
                        </div>
                        <span class="form-field__error" role="alert" aria-live="polite"></span>
                    </div>

                    <div class="form-field form-field--checkbox">
                        <label class="form-field__checkbox-label">
                            <input
                                class="form-field__checkbox"
                                type="checkbox"
                                name="AGREE"
                                required
                                value="Y"
                            >
                            <span class="form-field__checkbox-text">
                                Я соглашаюсь с
                                <a class="form-field__link" href="/policy/" target="_blank" rel="noopener">политикой конфиденциальности</a>
                            </span>
                        </label>
                        <span class="form-field__error" role="alert" aria-live="polite"></span>
                    </div>

                    <div class="modal-auth__form-footer">
                        <button class="btn btn--primary btn--full" type="submit">Зарегистрироваться</button>
                        <span class="modal-auth__form-error" role="alert" aria-live="assertive" hidden></span>
                    </div>
                </form>

                <!-- Соцсети для регистрации -->
                <?php if (!empty($arResult['SOCIAL_SERVICES'])): ?>
                <div class="modal-auth__social">
                    <p class="modal-auth__social-label">Или через</p>
                    <div class="modal-auth__social-list">
                        <?php if (in_array('vk', $arResult['SOCIAL_SERVICES'], true)): ?>
                        <a class="modal-auth__social-btn modal-auth__social-btn--vk" href="/auth/?auth_service_id=vkontakte" aria-label="Регистрация через ВКонтакте">
                            <svg class="modal-auth__social-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12.785 16.241s.288-.032.436-.194c.136-.148.131-.427.131-.427s-.019-1.304.587-1.496c.597-.189 1.364 1.26 2.176 1.816.614.421 1.08.329 1.08.329l2.17-.03s1.135-.07.597-1.195c-.044-.094-.314-.657-1.613-1.856-1.36-1.254-1.178-1.051.46-3.22.999-1.328 1.397-2.14 1.272-2.487-.12-.33-.855-.244-.855-.244l-2.44.015s-.181-.025-.315.056c-.132.08-.217.265-.217.265s-.386 1.03-.9 1.904c-1.086 1.842-1.52 1.94-1.697 1.825-.413-.266-.31-1.072-.31-1.644 0-1.788.271-2.533-.528-2.727-.265-.064-.46-.106-1.137-.113-.87-.009-1.605.003-2.022.207-.277.135-.49.437-.36.454.161.022.526.098.72.36.25.34.241 1.104.241 1.104s.144 2.104-.335 2.364c-.329.177-.78-.185-1.748-1.843-.497-.857-.872-1.805-.872-1.805s-.072-.178-.202-.274c-.157-.115-.376-.151-.376-.151l-2.32.015s-.349.01-.477.162c-.114.135-.009.414-.009.414s1.816 4.25 3.872 6.394c1.886 1.967 4.03 1.838 4.03 1.838h.97Z" fill="currentColor"/>
                            </svg>
                        </a>
                        <?php endif; ?>
                        <?php if (in_array('ok', $arResult['SOCIAL_SERVICES'], true)): ?>
                        <a class="modal-auth__social-btn modal-auth__social-btn--ok" href="/auth/?auth_service_id=odnoklassniki" aria-label="Регистрация через Одноклассники">
                            <svg class="modal-auth__social-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm4.5 9.75a7.1 7.1 0 0 1-2.75.75l1.75 1.75a.75.75 0 1 1-1.06 1.06L12 18.06l-2.44 2.25a.75.75 0 1 1-1.06-1.06l1.75-1.75a7.1 7.1 0 0 1-2.75-.75.75.75 0 1 1 .75-1.3A5.5 5.5 0 0 0 12 17.5a5.5 5.5 0 0 0 3.75-1.05.75.75 0 1 1 .75 1.3Z" fill="currentColor"/>
                            </svg>
                        </a>
                        <?php endif; ?>
                        <?php if (in_array('tg', $arResult['SOCIAL_SERVICES'], true)): ?>
                        <a class="modal-auth__social-btn modal-auth__social-btn--tg" href="/local/ajax/auth/telegram.php" aria-label="Регистрация через Telegram">
                            <svg class="modal-auth__social-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M9.036 15.572 8.704 20.1c.49 0 .702-.21.956-.461l2.294-2.196 4.754 3.48c.872.48 1.488.228 1.722-.802l3.122-14.6c.278-1.276-.464-1.774-1.314-1.46L1.986 10.208c-1.244.484-1.226 1.178-.212 1.49l4.572 1.42 10.618-6.666c.5-.33.956-.147.58.183L9.036 15.572Z" fill="currentColor"/>
                            </svg>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <!-- /РЕГИСТРАЦИЯ -->

            <?php else: ?>
            <!-- Авторизован: ссылки на разделы ЛК -->
            <nav class="modal-auth__lk-nav" aria-label="Личный кабинет">
                <a class="modal-auth__lk-link" href="/personal/">Мои заказы</a>
                <a class="modal-auth__lk-link" href="/personal/profile/">Профиль</a>
                <a class="modal-auth__lk-link" href="/personal/wishlist/">Избранное</a>
                <a class="modal-auth__lk-link" href="/personal/addresses/">Адреса доставки</a>
                <a class="modal-auth__lk-link modal-auth__lk-link--logout" href="/auth/?action=logout&<?= bitrix_sessid_url() ?>">Выйти</a>
            </nav>
            <?php endif; ?>
        </div>
    </div>
    <!-- /MODAL AUTH -->

    <!-- =======================================================
         MODAL: Адрес доставки
         Триггер: data-modal-open="delivery-address"
         ======================================================= -->
    <div class="modal__window modal__window--delivery" id="modal-delivery-address" role="document" aria-labelledby="modal-delivery-title" hidden>
        <button class="modal__close" type="button" data-modal-close aria-label="Закрыть">
            <svg class="modal__close-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M1 1L15 15M15 1L1 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </button>

        <div class="modal-delivery">
            <h2 class="modal-delivery__title" id="modal-delivery-title">Адрес доставки</h2>
            <p class="modal-delivery__description">Заполните поля адреса доставки, чтобы мы могли рассчитать стоимость и сроки</p>

            <form
                class="modal-delivery__form"
                id="form-delivery-address"
                method="post"
                action="/local/ajax/sale/address.php"
                novalidate
                data-ajax-form
                data-ajax-success="modal-delivery-success"
            >
                <?= bitrix_sessid_post() ?>
                <input type="hidden" name="ACTION" value="SAVE_ADDRESS">
                <input type="hidden" name="ADDRESS_ID" id="delivery-address-id" value="">

                <div class="form-field">
                    <label class="form-field__label" for="delivery-city">Город</label>
                    <input
                        class="form-field__input"
                        type="text"
                        id="delivery-city"
                        name="CITY"
                        autocomplete="address-level2"
                        required
                        placeholder="Москва"
                    >
                    <span class="form-field__error" role="alert" aria-live="polite"></span>
                </div>

                <div class="form-field">
                    <label class="form-field__label" for="delivery-street">Улица</label>
                    <input
                        class="form-field__input"
                        type="text"
                        id="delivery-street"
                        name="STREET"
                        autocomplete="address-line1"
                        required
                        placeholder="Ленина"
                    >
                    <span class="form-field__error" role="alert" aria-live="polite"></span>
                </div>

                <div class="modal-delivery__row">
                    <div class="form-field">
                        <label class="form-field__label" for="delivery-house">Дом</label>
                        <input
                            class="form-field__input"
                            type="text"
                            id="delivery-house"
                            name="HOUSE"
                            required
                            placeholder="1"
                        >
                        <span class="form-field__error" role="alert" aria-live="polite"></span>
                    </div>

                    <div class="form-field">
                        <label class="form-field__label" for="delivery-building">Корпус</label>
                        <input
                            class="form-field__input"
                            type="text"
                            id="delivery-building"
                            name="BUILDING"
                            placeholder="—"
                        >
                    </div>

                    <div class="form-field">
                        <label class="form-field__label" for="delivery-flat">Квартира</label>
                        <input
                            class="form-field__input"
                            type="text"
                            id="delivery-flat"
                            name="FLAT"
                            placeholder="—"
                        >
                    </div>
                </div>

                <div class="form-field">
                    <label class="form-field__label" for="delivery-zip">Индекс</label>
                    <input
                        class="form-field__input"
                        type="text"
                        id="delivery-zip"
                        name="ZIP"
                        autocomplete="postal-code"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        placeholder="123456"
                    >
                    <span class="form-field__error" role="alert" aria-live="polite"></span>
                </div>

                <div class="form-field form-field--checkbox">
                    <label class="form-field__checkbox-label">
                        <input
                            class="form-field__checkbox"
                            type="checkbox"
                            name="IS_DEFAULT"
                            value="Y"
                        >
                        <span class="form-field__checkbox-text">Сделать адресом по умолчанию</span>
                    </label>
                </div>

                <div class="modal-delivery__footer">
                    <button class="btn btn--primary btn--full" type="submit">Сохранить адрес</button>
                    <button class="btn btn--ghost btn--full" type="button" data-modal-close>Отмена</button>
                    <span class="modal-delivery__error" role="alert" aria-live="assertive" hidden></span>
                </div>
            </form>
        </div>
    </div>
    <!-- /MODAL DELIVERY ADDRESS -->

    <!-- =======================================================
         MODAL: Оценить гарантию
         Триггер: data-modal-open="warranty"
         ======================================================= -->
    <div class="modal__window modal__window--warranty" id="modal-warranty" role="document" aria-labelledby="modal-warranty-title" hidden>
        <button class="modal__close" type="button" data-modal-close aria-label="Закрыть">
            <svg class="modal__close-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M1 1L15 15M15 1L1 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </button>

        <div class="modal-warranty">
            <h2 class="modal-warranty__title" id="modal-warranty-title">Оценить гарантию</h2>
            <p class="modal-warranty__description">Оцените качество гарантийного обслуживания, чтобы мы могли улучшить сервис</p>

            <form
                class="modal-warranty__form"
                id="form-warranty"
                method="post"
                action="/local/ajax/warranty/rate.php"
                novalidate
                data-ajax-form
                data-ajax-success="modal-warranty-success"
            >
                <?= bitrix_sessid_post() ?>
                <input type="hidden" name="ORDER_ID" id="warranty-order-id" value="">

                <!-- Звёзды -->
                <div class="rating-stars rating-stars--input" role="group" aria-label="Оценка от 1 до 5">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input
                        class="rating-stars__input"
                        type="radio"
                        id="warranty-star-<?= $i ?>"
                        name="WARRANTY_RATING"
                        value="<?= $i ?>"
                        <?= $i === 5 ? 'checked' : '' ?>
                    >
                    <label class="rating-stars__label" for="warranty-star-<?= $i ?>" aria-label="<?= $i ?> звёзд">
                        <svg class="rating-stars__icon" width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                            <path d="M16 2.5l3.708 7.515 8.292 1.207-6 5.848 1.416 8.263L16 21.25l-7.416 3.083L10 16.07 4 10.222l8.292-1.207L16 2.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                        </svg>
                    </label>
                    <?php endfor; ?>
                </div>

                <div class="form-field">
                    <label class="form-field__label" for="warranty-comment">Комментарий</label>
                    <textarea
                        class="form-field__textarea"
                        id="warranty-comment"
                        name="WARRANTY_COMMENT"
                        rows="4"
                        placeholder="Опишите ваш опыт взаимодействия с гарантийным отделом..."
                    ></textarea>
                    <span class="form-field__error" role="alert" aria-live="polite"></span>
                </div>

                <div class="modal-warranty__footer">
                    <button class="btn btn--primary btn--full" type="submit">Отправить оценку</button>
                    <button class="btn btn--ghost btn--full" type="button" data-modal-close>Заполнить позже</button>
                    <span class="modal-warranty__error" role="alert" aria-live="assertive" hidden></span>
                </div>
            </form>

            <!-- Состояние: успех -->
            <div class="modal-warranty__success" id="modal-warranty-success" hidden>
                <p class="modal-warranty__success-text">Спасибо за оценку! Ваш отзыв помогает нам становиться лучше.</p>
                <button class="btn btn--primary" type="button" data-modal-close>Закрыть</button>
            </div>
        </div>
    </div>
    <!-- /MODAL WARRANTY -->

    <!-- =======================================================
         MODAL: Написать отзыв
         Триггер: data-modal-open="review"
         ======================================================= -->
    <div class="modal__window modal__window--review" id="modal-review" role="document" aria-labelledby="modal-review-title" hidden>
        <button class="modal__close" type="button" data-modal-close aria-label="Закрыть">
            <svg class="modal__close-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M1 1L15 15M15 1L1 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </button>

        <div class="modal-review">
            <h2 class="modal-review__title" id="modal-review-title">Написать отзыв</h2>
            <p class="modal-review__description">Ваш опыт поможет другим покупателям сделать правильный выбор</p>

            <form
                class="modal-review__form"
                id="form-review"
                method="post"
                action="/local/ajax/review/add.php"
                enctype="multipart/form-data"
                novalidate
                data-ajax-form
                data-ajax-success="modal-review-success"
            >
                <?= bitrix_sessid_post() ?>
                <input type="hidden" name="PRODUCT_ID" id="review-product-id" value="">

                <!-- Звёзды -->
                <div class="rating-stars rating-stars--input" role="group" aria-label="Общая оценка товара от 1 до 5">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input
                        class="rating-stars__input"
                        type="radio"
                        id="review-star-<?= $i ?>"
                        name="RATING"
                        value="<?= $i ?>"
                    >
                    <label class="rating-stars__label" for="review-star-<?= $i ?>" aria-label="<?= $i ?> звёзд">
                        <svg class="rating-stars__icon" width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                            <path d="M16 2.5l3.708 7.515 8.292 1.207-6 5.848 1.416 8.263L16 21.25l-7.416 3.083L10 16.07 4 10.222l8.292-1.207L16 2.5Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                        </svg>
                    </label>
                    <?php endfor; ?>
                </div>

                <!-- Имя (если не авторизован) -->
                <?php if (!$isAuth): ?>
                <div class="form-field">
                    <label class="form-field__label" for="review-author-name">Ваше имя</label>
                    <input
                        class="form-field__input"
                        type="text"
                        id="review-author-name"
                        name="AUTHOR_NAME"
                        autocomplete="name"
                        required
                        placeholder="Имя"
                    >
                    <span class="form-field__error" role="alert" aria-live="polite"></span>
                </div>
                <?php endif; ?>

                <div class="form-field">
                    <label class="form-field__label" for="review-pros">Достоинства</label>
                    <input
                        class="form-field__input"
                        type="text"
                        id="review-pros"
                        name="PROS"
                        placeholder="Что понравилось?"
                    >
                </div>

                <div class="form-field">
                    <label class="form-field__label" for="review-cons">Недостатки</label>
                    <input
                        class="form-field__input"
                        type="text"
                        id="review-cons"
                        name="CONS"
                        placeholder="Что не понравилось?"
                    >
                </div>

                <div class="form-field">
                    <label class="form-field__label" for="review-text">Комментарий</label>
                    <textarea
                        class="form-field__textarea"
                        id="review-text"
                        name="TEXT"
                        rows="4"
                        required
                        placeholder="Расскажите подробнее о товаре..."
                    ></textarea>
                    <span class="form-field__error" role="alert" aria-live="polite"></span>
                </div>

                <!-- Загрузка фото -->
                <div class="form-field">
                    <span class="form-field__label">Фотографии (до 5 шт., WebP/JPG, max 5 МБ каждое)</span>
                    <label class="form-field__file-label" for="review-photos" aria-describedby="review-photos-hint">
                        <svg class="form-field__file-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <span class="form-field__file-text">Добавить фото</span>
                        <input
                            class="form-field__file"
                            type="file"
                            id="review-photos"
                            name="PHOTOS[]"
                            accept="image/webp,image/jpeg,image/jpg"
                            multiple
                            data-max-files="5"
                            data-max-size="5242880"
                        >
                    </label>
                    <span class="form-field__hint" id="review-photos-hint">WebP или JPG, не более 5 файлов, до 5 МБ каждый</span>
                    <!-- Превью загруженных фото -->
                    <div class="form-field__file-preview" id="review-photos-preview" role="list" aria-label="Загруженные фото" hidden></div>
                    <span class="form-field__error" role="alert" aria-live="polite"></span>
                </div>

                <div class="modal-review__footer">
                    <button class="btn btn--primary btn--full" type="submit">Опубликовать отзыв</button>
                    <button class="btn btn--ghost btn--full" type="button" data-modal-close>Заполнить позже</button>
                    <span class="modal-review__error" role="alert" aria-live="assertive" hidden></span>
                </div>
            </form>

            <!-- Состояние: успех -->
            <div class="modal-review__success" id="modal-review-success" hidden>
                <p class="modal-review__success-text">Отзыв отправлен на модерацию. Спасибо!</p>
                <button class="btn btn--primary" type="button" data-modal-close>Закрыть</button>
            </div>
        </div>
    </div>
    <!-- /MODAL REVIEW -->

</div>
<!-- /MODAL SYSTEM -->
