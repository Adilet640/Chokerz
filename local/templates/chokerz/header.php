<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Application;
use Bitrix\Sale\Basket;
use Bitrix\Main\Loader;


$asset = Asset::getInstance();
$asset->addCss(SITE_TEMPLATE_PATH . '/styles/main.css');
$asset->addJs(SITE_TEMPLATE_PATH . '/js/main.js', true); // defer через параметр

// ── Счётчик товаров в корзине (Bitrix D7 Sale API) ───────────────────────────
$cartCount = 0;
if (Loader::includeModule('sale')) {
    $fUserId = \CSaleBasket::GetBasketUserID();
    $cartCount = (int) \CSaleBasket::GetBasketItemsCount($fUserId, SITE_ID);
}


$curPageDir = Application::getInstance()->getContext()->getRequest()->getRequestedPage();

/**
 * Вспомогательная функция: возвращает BEM-модификатор активности ссылки.
 *
 * @param  string $path    Начало пути для сравнения
 * @param  string $curDir  Текущий путь запроса
 * @return string          CSS-класс модификатора или пустая строка
 */
function chkNavActive(string $path, string $curDir): string
{
    return str_starts_with($curDir, $path) ? ' nav__link--active' : '';
}
?>
<!DOCTYPE html>
<html lang="ru" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">

    <?php
    /* SEO: мета-теги управляются из административной части */
    $APPLICATION->ShowMeta('description');
    $APPLICATION->ShowMeta('keywords');

    /* Canonical и Open Graph задаются в php_interface/include/events.php */
    ?>

    <title><?php $APPLICATION->ShowTitle() ?></title>

    <!-- Favicon -->
    <link rel="icon" href="<?= SITE_TEMPLATE_PATH ?>/images/favicon.ico" type="image/x-icon" sizes="any">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= SITE_TEMPLATE_PATH ?>/images/apple-touch-icon.png">

    <!-- Open Graph — значения устанавливаются через $APPLICATION->SetPageProperty() -->
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="https://<?= htmlspecialcharsbx(SITE_SERVER_NAME . $APPLICATION->GetCurPage()) ?>">
    <meta property="og:title"       content="<?php $APPLICATION->ShowTitle(false) ?>">
    <meta property="og:description" content="<?= htmlspecialcharsbx((string)$APPLICATION->GetProperty('og_description')) ?>">
    <meta property="og:image"       content="https://<?= htmlspecialcharsbx(SITE_SERVER_NAME) ?><?= SITE_TEMPLATE_PATH ?>/images/og-image.webp">
    <meta property="og:locale"      content="ru_RU">

    <?php $APPLICATION->ShowHead() ?>
<!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&family=Spectral:ital,wght@0,400;0,600;1,400&family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&family=IBM+Plex+Sans:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">

    <!-- Base -->
<link rel="stylesheet" href="/local/templates/chokerz/styles/base/variables.css">
<link rel="stylesheet" href="/local/templates/chokerz/styles/base/typography.css">

<!-- Layout -->
<link rel="stylesheet" href="/local/templates/chokerz/styles/layout/header.css">
<link rel="stylesheet" href="/local/templates/chokerz/styles/layout/footer.css">

<!-- Blocks -->
<link rel="stylesheet" href="/local/templates/chokerz/styles/blocks/hero.css">
<link rel="stylesheet" href="/local/templates/chokerz/styles/blocks/advantages.css">
<link rel="stylesheet" href="/local/templates/chokerz/styles/blocks/products.css">
<link rel="stylesheet" href="/local/templates/chokerz/styles/blocks/subscribe.css">
<link rel="stylesheet" href="/local/templates/chokerz/styles/blocks/catalog.css">
<link rel="stylesheet" href="/local/templates/chokerz/styles/blocks/filter.css">
<link rel="stylesheet" href="/local/templates/chokerz/styles/blocks/card.css">
<link rel="stylesheet" href="/local/templates/chokerz/styles/blocks/product-detail.css">
<link rel="stylesheet" href="/local/templates/chokerz/styles/blocks/checkout.css">
<link rel="stylesheet" href="/local/templates/chokerz/styles/blocks/lk.css">
<link rel="stylesheet" href="/local/templates/chokerz/styles/blocks/blog.css">
<link rel="stylesheet" href="/local/templates/chokerz/styles/blocks/modal.css">
<link rel="stylesheet" href="/local/templates/chokerz/styles/blocks/search.css">
</head>
<body class="body<?= ($APPLICATION->GetProperty('body_class') ? ' ' . htmlspecialcharsbx($APPLICATION->GetProperty('body_class')) : '') ?>">

<?php /* Панель управления (показывается авторизованным пользователям в режиме правки) */ ?>
<div id="panel"><?php $APPLICATION->ShowPanel() ?></div>

<!-- ════════════════════════════════════════════════════════════════════════════
     TOP BAR — информационная строка над шапкой
     ════════════════════════════════════════════════════════════════════════════ -->
<div class="header-top" role="banner">
    <div class="header-top__container container">

        <address class="header-top__contacts">
            <a href="tel:+74951234567" class="header-top__link">
                <!-- SVG: телефон -->
                <svg class="header-top__icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true" focusable="false">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.13 11.91 19.79 19.79 0 0 1 1.06 3.22 2 2 0 0 1 3.05 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
                <span>+7 (495) 123-45-67</span>
            </a>
            <a href="mailto:info@chokerz.ru" class="header-top__link">
                <!-- SVG: email -->
                <svg class="header-top__icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true" focusable="false">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
                <span>info@chokerz.ru</span>
            </a>
        </address>

        <nav class="header-top__social" aria-label="Социальные сети">
            <a href="https://vk.com/chokerz"
               class="header-top__social-link"
               target="_blank" rel="noopener noreferrer"
               aria-label="ВКонтакте">
                <!-- SVG: VK -->
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"
                     aria-hidden="true" focusable="false">
                    <path d="M21.547 7h-3.29a.743.743 0 0 0-.655.392s-1.312 2.416-1.734 3.23C14.734 12.813 14 12.126 14 11.11V7.603A1.104 1.104 0 0 0 12.896 6.5h-2.474a1.982 1.982 0 0 0-1.75.813s1.255-.204 1.255 1.49c0 .42.022 1.626.04 2.64a.73.73 0 0 1-1.272.503 21.54 21.54 0 0 1-2.498-4.543.693.693 0 0 0-.63-.403h-2.99a.508.508 0 0 0-.48.685C3.005 10.175 6.918 18 11.38 18h1.878a.742.742 0 0 0 .742-.742v-1.23a.764.764 0 0 1 1.388-.43l1.018 1.869a1.089 1.089 0 0 0 .964.582h2.752a1.149 1.149 0 0 0 .782-1.928l-1.257-1.386c-.81-.892-.602-1.312.22-2.458l2.1-2.88A1.149 1.149 0 0 0 21.547 7z"/>
                </svg>
            </a>
            <a href="https://t.me/chokerz"
               class="header-top__social-link"
               target="_blank" rel="noopener noreferrer"
               aria-label="Telegram">
                <!-- SVG: Telegram -->
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"
                     aria-hidden="true" focusable="false">
                    <path d="m20.665 3.717-17.73 6.837c-1.21.486-1.203 1.161-.222 1.462l4.552 1.42 10.532-6.645c.498-.303.953-.14.579.192L9.982 14.02l-.392 5.082c.574 0 .827-.264 1.148-.575l2.757-2.68 5.728 4.23c1.056.58 1.815.282 2.078-.98l3.762-17.718c.39-1.566-.597-2.274-1.398-1.662z"/>
                </svg>
            </a>
            <a href="https://www.ozon.ru/seller/10238"
               class="header-top__social-link"
               target="_blank" rel="noopener noreferrer"
               aria-label="OZON">
                <span class="header-top__marketplace-label">OZON</span>
            </a>
            <a href="https://www.wildberries.ru/seller/48237"
               class="header-top__social-link"
               target="_blank" rel="noopener noreferrer"
               aria-label="Wildberries">
                <span class="header-top__marketplace-label">WB</span>
            </a>
        </nav>

    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════════════════
     HEADER — основная шапка
     ════════════════════════════════════════════════════════════════════════════ -->
<header class="header" role="banner" id="site-header">
    <div class="header__container container">

        <!-- ── Логотип ─────────────────────────────────────────────────────── -->
        <a href="/" class="header__logo" aria-label="CHOKERZ — амуниция для животных, перейти на главную">
            <!--
                Inline SVG логотипа:
                - fill="currentColor" позволяет управлять цветом через CSS .header__logo
                - aria-hidden="true" т.к. ссылка имеет aria-label
            -->
            <svg class="header__logo-icon"
                 xmlns="http://www.w3.org/2000/svg"
                 viewBox="0 0 204.8 153.6"
                 width="48" height="36"
                 fill="currentColor"
                 aria-hidden="true"
                 focusable="false">
                <g transform="translate(0,153.6) scale(0.01,-0.01)" stroke="none">
                    <path d="M10185 12489c-1093-71-2068-548-2779-1359-544-619-881-1402-967-2240-17-162-17-600 0-760 67-654 279-1259 627-1793 346-529 789-951 1339-1274 523-307 1157-498 1758-531 72-4 977-6 2010-4l1877 3 0 645 0 644-1892 0c-1559 0-1915 3-2018 14-814 92-1532 537-1967 1221-511 802-556 1821-117 2664 135 260 261 429 483 652 237 237 423 371 706 510 237 116 413 176 650 224 239 48 193 47 2228 45l1927-1 0 670 0 671-1839 0c-1011 0-1851 2-1867 4-16 2-87 0-159-5z"/>
                    <path d="M6825 3941c-196-57-359-234-402-435-47-227 79-472 300-581 209-103 457-73 616 74 36 33 41 42 32 59-6 11-57 63-115 116l-105 98-43-30c-53-36-77-44-137-44-59 1-90 13-135 53-104 91-105 236-2 326 46 41 96 56 166 51 41-3 70-12 99-31 22-15 46-27 53-27 7 0 63 47 123 104l110 104-47 45c-58 54-145 101-223 122-78 20-214 18-290-4z"/>
                    <path d="M9123 3946c-191-46-370-223-412-406-16-68-13-209 5-271 42-144 151-274 285-342 170-85 337-86 506-2 201 100 313 273 313 484 0 160-46 267-161 381-82 81-161 128-264 154-70 19-199 19-272 2zm233-335c151-69 172-281 36-375-147-102-352 2-352 179 0 157 170 262 316 196z"/>
                    <path d="M7541 3936c-8-9-10-157-9-532l3-519 165 0 165 0 3 203 2 202 155 0 155 0 2-202 3-203 160-3c142-2 161-1 172 15 10 13 13 132 13 519 0 444-2 504-16 518-13 13-42 16-163 16-101 0-151-4-159-12-8-8-12-60-12-170l0-158-155 0-155 0 0 164c0 140-2 165-16 170-9 3-80 6-159 6-108 0-145-3-154-14z"/>
                    <path d="M10001 3936c-8-9-10-157-9-532l3-519 168-3c92-1 168-1 169 0 1 2 4 98 7 215l6 211 114-201c63-111 122-207 130-214 19-15 381-18 381-4 0 5-70 131-155 280l-155 271 150 242c83 133 150 248 150 255 0 10-42 13-184 13-178 0-186-1-210-22-14-13-71-99-126-193l-100-170 0 177c0 147-3 179-16 192-21 22-305 24-323 2z"/>
                    <path d="M11127 3943c-4-3-7-64-7-135l0-128 405 0 406 0-3 133-3 132-396 3c-217 1-398-1-402-5z"/>
                    <path d="M12120 3931c-11-21-15-1007-4-1035 9-24 329-24 338 0 3 9 6 85 6 170 0 209 7 208 120-14l85-167 168-3c141-2 168 0 173 13 3 8-38 95-95 201-56 102-101 188-101 191 0 3 22 25 49 49 29 26 62 69 78 102 25 51 28 68 28 157 0 91-3 105-29 158-33 63-93 125-153 155-65 34-146 42-404 42-235 0-249-1-259-19zm495-281c35-33 35-88-1-121-22-20-35-24-92-24l-67 0-3 74c-4 104-4 104 73 99 50-3 70-9 90-28z"/>
                    <path d="M13162 3938c-13-13-18-232-6-262 5-14 35-16 225-16 120 0 219-3 219-6 0-3-99-127-220-275l-221-270 3-112 3-112 430-3c395-2 431-1 443 15 17 24 17 247-1 262-9 7-91 12-230 13l-217 3 225 279 225 278 0 103c0 85-3 104-16 109-9 3-203 6-433 6-311 0-420-3-429-12z"/>
                    <path d="M11122 3408l3-133 400 0 400 0 3 133 3 132-406 0-406 0 3-132z"/>
                    <path d="M11127 3143c-4-3-7-64-7-135l0-128 405 0 406 0-3 133-3 132-396 3c-217 1-398-1-402-5z"/>
                </g>
            </svg>
            <span class="header__logo-text">CHOKERZ</span>
        </a>

        <!-- ── Основная навигация ───────────────────────────────────────────── -->
        <nav class="header__nav nav" id="main-nav" aria-label="Основное меню">
            <ul class="nav__list" role="list">
                <li class="nav__item">
                    <a href="/catalog/"
                       class="nav__link<?= chkNavActive('/catalog/', $curPageDir) ?>"
                       <?= str_starts_with($curPageDir, '/catalog/') ? 'aria-current="page"' : '' ?>>
                        Каталог
                    </a>
                </li>
                <li class="nav__item">
                    <a href="/about/"
                       class="nav__link<?= chkNavActive('/about/', $curPageDir) ?>"
                       <?= str_starts_with($curPageDir, '/about/') ? 'aria-current="page"' : '' ?>>
                        О нас
                    </a>
                </li>
                <li class="nav__item">
                    <a href="/delivery/"
                       class="nav__link<?= chkNavActive('/delivery/', $curPageDir) ?>"
                       <?= str_starts_with($curPageDir, '/delivery/') ? 'aria-current="page"' : '' ?>>
                        Доставка и оплата
                    </a>
                </li>
                <li class="nav__item">
                    <a href="/blog/"
                       class="nav__link<?= chkNavActive('/blog/', $curPageDir) ?>"
                       <?= str_starts_with($curPageDir, '/blog/') ? 'aria-current="page"' : '' ?>>
                        Блог
                    </a>
                </li>
                <li class="nav__item">
                    <a href="/wholesale/"
                       class="nav__link<?= chkNavActive('/wholesale/', $curPageDir) ?>"
                       <?= str_starts_with($curPageDir, '/wholesale/') ? 'aria-current="page"' : '' ?>>
                        Опт
                    </a>
                </li>
                <li class="nav__item">
                    <a href="/contacts/"
                       class="nav__link<?= chkNavActive('/contacts/', $curPageDir) ?>"
                       <?= str_starts_with($curPageDir, '/contacts/') ? 'aria-current="page"' : '' ?>>
                        Контакты
                    </a>
                </li>
            </ul>
        </nav>

        <!-- ── Правая группа: поиск + иконки ───────────────────────────────── -->
        <div class="header__right">

            <!-- Поисковая форма -->
            <div class="header__search search" id="search-widget" role="search">
                <form class="search__form"
                      action="/catalog/"
                      method="get"
                      autocomplete="off"
                      aria-label="Поиск по сайту">
                    <label for="search-input" class="search__label visually-hidden">Поиск</label>
                    <input type="search"
                           id="search-input"
                           name="q"
                           class="search__input"
                           placeholder="Поиск по сайту..."
                           maxlength="255"
                           aria-label="Строка поиска">
                    <button type="button"
                            class="search__clear"
                            id="search-clear"
                            aria-label="Очистить поиск"
                            hidden>
                        <!-- SVG: крестик очистки -->
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             aria-hidden="true" focusable="false">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                    <button type="submit" class="search__btn" aria-label="Найти">
                        <!-- SVG: лупа -->
                        <svg class="search__icon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             aria-hidden="true" focusable="false">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Иконка: открыть поиск на мобильном -->
            <button type="button"
                    class="header__action header__action--search-toggle"
                    id="search-toggle-mobile"
                    aria-label="Открыть поиск"
                    aria-expanded="false"
                    aria-controls="search-widget">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true" focusable="false">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
            </button>

            <!-- Иконка: избранное (wishlist) -->
            <a href="/personal/wishlist/"
               class="header__action header__action--wishlist"
               aria-label="Избранное"
               data-wishlist-trigger>
                <!-- SVG: сердце -->
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true" focusable="false">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                <span class="header__badge"
                      data-wishlist-count
                      aria-label="Товаров в избранном: 0"
                      hidden>0</span>
            </a>

            <!-- Иконка: личный кабинет -->
            <a href="/personal/"
               class="header__action header__action--profile"
               aria-label="Личный кабинет">
                <!-- SVG: профиль -->
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true" focusable="false">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </a>

            <!-- Иконка: корзина -->
            <a href="/cart/"
               class="header__action header__action--cart"
               aria-label="Корзина, товаров: <?= $cartCount ?>"
               data-cart-trigger>
                <!-- SVG: корзина -->
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true" focusable="false">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                <span class="header__badge"
                      data-cart-count
                      aria-label="Товаров в корзине: <?= $cartCount ?>"
                      <?= $cartCount === 0 ? 'hidden' : '' ?>>
                    <?= $cartCount ?>
                </span>
            </a>

        </div>

        <!-- ── Бургер (только mobile) ──────────────────────────────────────── -->
        <button type="button"
                class="header__burger burger"
                id="burger-btn"
                aria-label="Открыть меню"
                aria-expanded="false"
                aria-controls="mobile-menu">
            <span class="burger__line" aria-hidden="true"></span>
            <span class="burger__line" aria-hidden="true"></span>
            <span class="burger__line" aria-hidden="true"></span>
        </button>

    </div>
</header>

<!-- ════════════════════════════════════════════════════════════════════════════
     MOBILE MENU — drawer (управляется mobile-menu.js)
     ════════════════════════════════════════════════════════════════════════════ -->
<div class="mobile-menu"
     id="mobile-menu"
     role="dialog"
     aria-modal="true"
     aria-label="Мобильное меню"
     hidden>

    <div class="mobile-menu__overlay" id="mobile-menu-overlay" aria-hidden="true"></div>

    <div class="mobile-menu__panel">
        <div class="mobile-menu__header">
            <a href="/" class="mobile-menu__logo" aria-label="CHOKERZ — перейти на главную">
                <span class="mobile-menu__logo-text">CHOKERZ</span>
            </a>
            <button type="button"
                    class="mobile-menu__close"
                    aria-label="Закрыть меню"
                    data-mobile-menu-close>
                <!-- SVG: крестик -->
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     aria-hidden="true" focusable="false">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <!-- Мобильный поиск -->
        <div class="mobile-menu__search" role="search">
            <form class="search__form search__form--mobile"
                  action="/catalog/"
                  method="get"
                  autocomplete="off"
                  aria-label="Поиск по сайту">
                <label for="mobile-search-input" class="visually-hidden">Поиск</label>
                <input type="search"
                       id="mobile-search-input"
                       name="q"
                       class="search__input"
                       placeholder="Поиск по сайту..."
                       maxlength="255">
                <button type="submit" class="search__btn" aria-label="Найти">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true" focusable="false">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                </button>
            </form>
        </div>

        <!-- Мобильная навигация -->
        <nav class="mobile-menu__nav" aria-label="Мобильное меню">
            <ul class="mobile-menu__list" role="list">
                <li class="mobile-menu__item">
                    <a href="/catalog/" class="mobile-menu__link">Каталог</a>
                </li>
                <li class="mobile-menu__item">
                    <a href="/about/" class="mobile-menu__link">О нас</a>
                </li>
                <li class="mobile-menu__item">
                    <a href="/delivery/" class="mobile-menu__link">Доставка и оплата</a>
                </li>
                <li class="mobile-menu__item">
                    <a href="/blog/" class="mobile-menu__link">Блог</a>
                </li>
                <li class="mobile-menu__item">
                    <a href="/wholesale/" class="mobile-menu__link">Опт</a>
                </li>
                <li class="mobile-menu__item">
                    <a href="/contacts/" class="mobile-menu__link">Контакты</a>
                </li>
            </ul>
        </nav>

        <!-- Мобильные контакты -->
        <div class="mobile-menu__contacts">
            <a href="tel:+74951234567" class="mobile-menu__contact-link">+7 (495) 123-45-67</a>
            <a href="mailto:info@chokerz.ru" class="mobile-menu__contact-link">info@chokerz.ru</a>
        </div>

    </div>
</div>

<!-- Разделитель: открытие тега main (закрывается в footer.php) -->
<main class="main" id="main-content">
