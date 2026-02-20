<?php
/**
 * Header шаблона сайта CHOKERZ
 * Шапка сайта с навигацией, поиском и корзиной
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Page\Asset;

Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/styles/main.css");
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/js/main.js");
?>
<!DOCTYPE html>
<html lang="ru" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    
    <?php
    // SEO мета-теги
    $APPLICATION->ShowMeta("description");
    $APPLICATION->ShowMeta("keywords");
    ?>
    
    <title><?php $APPLICATION->ShowTitle() ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="<?= SITE_TEMPLATE_PATH ?>/images/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="<?= SITE_TEMPLATE_PATH ?>/images/apple-touch-icon.png">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php $APPLICATION->ShowTitle() ?>">
    <meta property="og:description" content="<?php $APPLICATION->ShowProperty('description') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $APPLICATION->GetCurPageParam('', array()) ?>">
    <meta property="og:image" content="<?= SITE_TEMPLATE_PATH ?>/images/og-image.jpg">
    
    <?php $APPLICATION->ShowHead() ?>
</head>
<body class="body <?= $APPLICATION->GetProperty('body_class') ?>">

<div id="panel"><?php $APPLICATION->ShowPanel() ?></div>

<header class="header">
    <div class="header__container container">
        <!-- Логотип -->
        <a href="/" class="header__logo">
            <img src="<?= SITE_TEMPLATE_PATH ?>/images/logo.svg" alt="CHOKERZ — амуниция для животных" class="header__logo-img">
            <span class="header__logo-text">CHOKERZ</span>
        </a>
        
        <!-- Навигация -->
        <nav class="header__nav nav" id="mainNav">
            <ul class="nav__list">
                <li class="nav__item">
                    <a href="/catalog/" class="nav__link">Каталог</a>
                </li>
                <li class="nav__item">
                    <a href="/about/" class="nav__link">О нас</a>
                </li>
                <li class="nav__item">
                    <a href="/delivery/" class="nav__link">Доставка и оплата</a>
                </li>
                <li class="nav__item">
                    <a href="/blog/" class="nav__link">Блог</a>
                </li>
                <li class="nav__item">
                    <a href="/contacts/" class="nav__link">Контакты</a>
                </li>
            </ul>
        </nav>
        
        <!-- Поиск -->
        <div class="header__search search">
            <form action="/search/" method="get" class="search__form">
                <input type="search" name="q" placeholder="Поиск товаров..." class="search__input" autocomplete="off">
                <button type="submit" class="search__btn">
                    <svg class="search__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </form>
        </div>
        
        <!-- Действия пользователя -->
        <div class="header__actions actions">
            <!-- Избранное -->
            <a href="/wishlist/" class="actions__btn actions__btn--wishlist" title="Избранное">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                <span class="actions__count" data-wishlist-count>0</span>
            </a>
            
            <!-- Профиль -->
            <a href="/personal/" class="actions__btn actions__btn--profile" title="Личный кабинет">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </a>
            
            <!-- Корзина -->
            <a href="/cart/" class="actions__btn actions__btn--cart" title="Корзина">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <span class="actions__count" data-cart-count>0</span>
            </a>
        </div>
        
        <!-- Мобильное меню -->
        <button class="header__burger burger" id="burgerBtn" aria-label="Меню">
            <span class="burger__line"></span>
            <span class="burger__line"></span>
            <span class="burger__line"></span>
        </button>
    </div>
</header>

<main class="main">
