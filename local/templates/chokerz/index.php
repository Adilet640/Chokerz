<?php
/**
 * Главная страница сайта CHOKERZ
 * Основной шаблон главной страницы
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$APPLICATION->SetTitle("CHOKERZ — амуниция для животных");
$APPLICATION->SetPageProperty("description", "Качественная амуниция для ваших питомцев. Ошейники, поводки, аксессуары. Доставка по всей России.");
$APPLICATION->SetPageProperty("keywords", "амуниция для животных, ошейники, поводки, аксессуары для собак, зоотовары");
$APPLICATION->SetPageProperty("body_class", "page-home");
?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php"); ?>

<!-- Герой-секция -->
<section class="hero">
    <div class="hero__container container">
        <div class="hero__content">
            <h1 class="hero__title">Амуниция для ваших питомцев</h1>
            <p class="hero__subtitle">Качество и стиль в каждой детали</p>
            <a href="/catalog/" class="hero__btn btn btn--primary">Перейти в каталог</a>
        </div>
        <div class="hero__image">
            <img src="<?= SITE_TEMPLATE_PATH ?>/images/hero-image.webp" alt="Амуниция CHOKERZ" class="hero__img">
        </div>
    </div>
</section>

<!-- Преимущества -->
<section class="advantages section">
    <div class="advantages__container container">
        <h2 class="advantages__title section-title">Почему выбирают нас</h2>
        
        <?php $APPLICATION->IncludeComponent(
            "bitrix:news.list",
            "advantages",
            array(
                "IBLOCK_TYPE" => "content",
                "IBLOCK_ID" => "advantages", // Инфоблок Преимущества
                "NEWS_COUNT" => "4",
                "SORT_BY1" => "SORT",
                "SORT_ORDER1" => "ASC",
                "FIELD_CODE" => array("NAME", "PREVIEW_PICTURE", "DETAIL_TEXT"),
                "PROPERTY_CODE" => array(),
                "CHECK_DATES" => "Y",
                "AJAX_MODE" => "N",
                "AJAX_OPTION_JUMP" => "N",
                "AJAX_OPTION_STYLE" => "Y",
                "AJAX_OPTION_HISTORY" => "N",
                "CACHE_TYPE" => "A",
                "CACHE_TIME" => "3600000",
                "CACHE_FILTER" => "N",
                "CACHE_GROUPS" => "Y",
                "PREVIEW_TRUNCATE_LEN" => "",
                "ACTIVE_DATE_FORMAT" => "d.m.Y",
                "SET_TITLE" => "N",
                "SET_BROWSER_TITLE" => "N",
                "SET_META_KEYWORDS" => "N",
                "SET_META_DESCRIPTION" => "N",
                "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                "PARENT_SECTION" => "",
                "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                "DISPLAY_TOP_PAGER" => "N",
                "DISPLAY_BOTTOM_PAGER" => "N",
                "PAGER_TITLE" => "",
                "PAGER_SHOW_ALWAYS" => "N",
                "PAGER_TEMPLATE" => "",
                "PAGER_NUM_PAGES" => "0",
                "PAGER_DESC_NUMBERING" => "N",
                "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                "PAGER_SHOW_ALL" => "N"
            ),
            false
        ); ?>
    </div>
</section>

<!-- Хиты продаж -->
<section class="products-hit section">
    <div class="products-hit__container container">
        <div class="products-hit__header section-header">
            <h2 class="products-hit__title section-title">Хиты продаж</h2>
            <a href="/catalog/?hit=Y" class="products-hit__link link">Смотреть все</a>
        </div>
        
        <?php $APPLICATION->IncludeComponent(
            "bitrix:catalog.section",
            "hits",
            array(
                "IBLOCK_TYPE" => "catalog",
                "IBLOCK_ID" => "catalog_products",
                "SECTION_ID" => "",
                "SECTION_CODE" => "",
                "ELEMENT_SORT_FIELD" => "hits",
                "ELEMENT_SORT_ORDER" => "DESC",
                "ELEMENT_COUNT" => "8",
                "LINE_ELEMENT_COUNT" => "4",
                "PROPERTY_CODE" => array(
                    "material",
                    "size",
                    "color"
                ),
                "SECTION_URL" => "",
                "DETAIL_URL" => "#SITE_DIR#catalog/#CODE#/",
                "BASKET_URL" => "#SITE_DIR#cart/",
                "ACTION" => "ADD_TO_BASKET",
                "PRODUCT_PROPERTIES" => array(),
                "USE_PRODUCT_QUANTITY" => "N",
                "ADD_PROPERTIES_TO_BASKET" => "Y",
                "PARTIAL_PRODUCT_PROPERTIES" => "N",
                "OFFERS_CART_PROPERTIES" => array(),
                "OFFERS_FIELD_CODE" => array(),
                "OFFERS_PROPERTY_CODE" => array(),
                "OFFERS_SORT_FIELD" => "sort",
                "OFFERS_SORT_ORDER" => "asc",
                "OFFERS_LIMIT" => "0",
                "PRICE_CODE" => array("BASE"),
                "USE_PRICE_COUNT" => "N",
                "SHOW_PRICE_COUNT" => "1",
                "PRICE_VAT_INCLUDE" => "Y",
                "CONVERT_CURRENCY" => "Y",
                "CURRENCY_ID" => "RUB",
                "DISPLAY_TOP_PAGER" => "N",
                "DISPLAY_BOTTOM_PAGER" => "N",
                "PAGER_TITLE" => "Товары",
                "PAGER_SHOW_ALWAYS" => "N",
                "PAGER_TEMPLATE" => "",
                "PAGER_DESC_NUMBERING" => "N",
                "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                "PAGER_SHOW_ALL" => "N",
                "CACHE_TYPE" => "A",
                "CACHE_TIME" => "3600000",
                "CACHE_FILTER" => "Y",
                "CACHE_GROUPS" => "Y",
                "FILTER_NAME" => "",
                "OFFERS_FIELD_CODE" => array(),
                "LABEL_PROP" => "",
                "PRODUCT_DISPLAY_MODE" => "Y",
                "ADD_PICT_PROP" => "MORE_PHOTO",
                "LABEL" => "",
                "MESS_BTN_BUY" => "Купить",
                "MESS_BTN_ADD_BASKET" => "В корзину",
                "MESS_BTN_COMPARE" => "Сравнить",
                "MESS_BTN_DETAIL" => "Подробнее",
                "MESS_NOT_AVAILABLE" => "Нет в наличии",
                "USE_MAIN_ELEMENT_SECTION" => "Y"
            ),
            false
        ); ?>
    </div>
</section>

<!-- Новинки -->
<section class="products-new section">
    <div class="products-new__container container">
        <div class="products-new__header section-header">
            <h2 class="products-new__title section-title">Новинки</h2>
            <a href="/catalog/?new=Y" class="products-new__link link">Смотреть все</a>
        </div>
        
        <?php $APPLICATION->IncludeComponent(
            "bitrix:catalog.section",
            "new",
            array(
                "IBLOCK_TYPE" => "catalog",
                "IBLOCK_ID" => "catalog_products",
                "SECTION_ID" => "",
                "SECTION_CODE" => "",
                "ELEMENT_SORT_FIELD" => "date_create",
                "ELEMENT_SORT_ORDER" => "DESC",
                "ELEMENT_COUNT" => "8",
                "LINE_ELEMENT_COUNT" => "4",
                "PROPERTY_CODE" => array(
                    "material",
                    "size",
                    "color"
                ),
                "SECTION_URL" => "",
                "DETAIL_URL" => "#SITE_DIR#catalog/#CODE#/",
                "BASKET_URL" => "#SITE_DIR#cart/",
                "ACTION" => "ADD_TO_BASKET",
                "PRODUCT_PROPERTIES" => array(),
                "USE_PRODUCT_QUANTITY" => "N",
                "ADD_PROPERTIES_TO_BASKET" => "Y",
                "PARTIAL_PRODUCT_PROPERTIES" => "N",
                "OFFERS_CART_PROPERTIES" => array(),
                "OFFERS_FIELD_CODE" => array(),
                "OFFERS_PROPERTY_CODE" => array(),
                "OFFERS_SORT_FIELD" => "sort",
                "OFFERS_SORT_ORDER" => "asc",
                "OFFERS_LIMIT" => "0",
                "PRICE_CODE" => array("BASE"),
                "USE_PRICE_COUNT" => "N",
                "SHOW_PRICE_COUNT" => "1",
                "PRICE_VAT_INCLUDE" => "Y",
                "CONVERT_CURRENCY" => "Y",
                "CURRENCY_ID" => "RUB",
                "DISPLAY_TOP_PAGER" => "N",
                "DISPLAY_BOTTOM_PAGER" => "N",
                "PAGER_TITLE" => "Товары",
                "PAGER_SHOW_ALWAYS" => "N",
                "PAGER_TEMPLATE" => "",
                "PAGER_DESC_NUMBERING" => "N",
                "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                "PAGER_SHOW_ALL" => "N",
                "CACHE_TYPE" => "A",
                "CACHE_TIME" => "3600000",
                "CACHE_FILTER" => "Y",
                "CACHE_GROUPS" => "Y",
                "FILTER_NAME" => "",
                "LABEL_PROP" => "NEW",
                "PRODUCT_DISPLAY_MODE" => "Y",
                "ADD_PICT_PROP" => "MORE_PHOTO",
                "LABEL" => "NEW",
                "MESS_BTN_BUY" => "Купить",
                "MESS_BTN_ADD_BASKET" => "В корзину",
                "MESS_BTN_COMPARE" => "Сравнить",
                "MESS_BTN_DETAIL" => "Подробнее",
                "MESS_NOT_AVAILABLE" => "Нет в наличии",
                "USE_MAIN_ELEMENT_SECTION" => "Y"
            ),
            false
        ); ?>
    </div>
</section>

<!-- Блог -->
<section class="blog section">
    <div class="blog__container container">
        <div class="blog__header section-header">
            <h2 class="blog__title section-title">Блог</h2>
            <a href="/blog/" class="blog__link link">Все статьи</a>
        </div>
        
        <?php $APPLICATION->IncludeComponent(
            "bitrix:news.list",
            "blog",
            array(
                "IBLOCK_TYPE" => "content",
                "IBLOCK_ID" => "blog",
                "NEWS_COUNT" => "3",
                "SORT_BY1" => "ACTIVE_FROM",
                "SORT_ORDER1" => "DESC",
                "FIELD_CODE" => array("NAME", "PREVIEW_PICTURE", "PREVIEW_TEXT"),
                "PROPERTY_CODE" => array(),
                "CHECK_DATES" => "Y",
                "AJAX_MODE" => "N",
                "AJAX_OPTION_JUMP" => "N",
                "AJAX_OPTION_STYLE" => "Y",
                "AJAX_OPTION_HISTORY" => "N",
                "CACHE_TYPE" => "A",
                "CACHE_TIME" => "3600000",
                "CACHE_FILTER" => "N",
                "CACHE_GROUPS" => "Y",
                "PREVIEW_TRUNCATE_LEN" => "100",
                "ACTIVE_DATE_FORMAT" => "d.m.Y",
                "SET_TITLE" => "N",
                "SET_BROWSER_TITLE" => "N",
                "SET_META_KEYWORDS" => "N",
                "SET_META_DESCRIPTION" => "N",
                "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                "PARENT_SECTION" => "",
                "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                "DISPLAY_TOP_PAGER" => "N",
                "DISPLAY_BOTTOM_PAGER" => "N",
                "PAGER_TITLE" => "",
                "PAGER_SHOW_ALWAYS" => "N",
                "PAGER_TEMPLATE" => "",
                "PAGER_NUM_PAGES" => "0",
                "PAGER_DESC_NUMBERING" => "N",
                "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                "PAGER_SHOW_ALL" => "N"
            ),
            false
        ); ?>
    </div>
</section>

<!-- Форма подписки -->
<section class="subscribe section">
    <div class="subscribe__container container">
        <div class="subscribe__content">
            <h2 class="subscribe__title">Подпишитесь на новости</h2>
            <p class="subscribe__desc">Узнавайте первыми о новинках и акциях</p>
            
            <?php $APPLICATION->IncludeComponent(
                "bitrix:form.result.new",
                "subscribe",
                array(
                    "WEB_FORM_ID" => "subscribe", // ID формы подписки
                    "RESULT_ID" => "",
                    "SEF_MODE" => "N",
                    "CACHE_TYPE" => "A",
                    "CACHE_TIME" => "3600",
                    "AJAX_MODE" => "Y",
                    "AJAX_OPTION_SHADOW" => "Y",
                    "AJAX_OPTION_JUMP" => "N",
                    "AJAX_OPTION_STYLE" => "Y",
                    "AJAX_OPTION_HISTORY" => "N",
                    "EDIT_KEYS" => "N",
                    "CHAIN_ITEM_TEXT" => "",
                    "CHAIN_ITEM_LINK" => "",
                    "IGNORE_CUSTOM_TEMPLATE" => "N",
                    "USE_EXTENDED_ERRORS" => "Y"
                ),
                false,
                array("HIDE_ICONS" => "Y")
            ); ?>
        </div>
    </div>
</section>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
