<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Page\Asset;

Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/styles/blocks/catalog.css');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/js/modules/catalog.js', false, ['defer' => true]);

// ── Данные раздела ────────────────────────────────────────────────────────────
$sectionData  = $arResult['SECTION']    ?? [];
$sectionName  = htmlspecialchars($sectionData['NAME'] ?? 'Каталог', ENT_QUOTES, 'UTF-8');
$sectionDesc  = $sectionData['DESCRIPTION'] ?? '';
$sectionPic   = $sectionData['PICTURE']['SRC'] ?? '';

// ── Пагинация ─────────────────────────────────────────────────────────────────
$navParam     = $arResult['NAV_PARAM_NAME'] ?? 'PAGEN_1';
$currentPage  = (int)($arResult['NAV_RESULT']->NavPageNomer ?? 1);
$totalPages   = (int)($arResult['NAV_RESULT']->NavPageCount ?? 1);
$pageCount    = (int)($arResult['NAV_RESULT']->NavRecordCount ?? 0);
$totalItems   = (int)($arResult['NAV_RESULT']->NavRecordCount ?? 0);
$isFirstPage  = $currentPage === 1;
$hasNextPage  = $currentPage < $totalPages;

// ── Канонический URL (ТЗ п.8.2) ──────────────────────────────────────────────
$canonicalUrl = $APPLICATION->GetCurPage(false);
if ($currentPage > 1) {
    // Страницы пагинации 2+ каноникал = родительская категория
    $APPLICATION->SetPageProperty('canonical', $canonicalUrl);
}
// Страница 1 → 301 на категорию (реализуется через php_interface/include/events.php)

// ── Кол-во товаров на текущей странице ────────────────────────────────────────
$itemsOnPage  = count($arResult['ITEMS'] ?? []);

// ── Сортировка (из GET параметров) ────────────────────────────────────────────
$sortOptions = [
    'date_desc'     => 'Новые сначала',
    'price_asc'     => 'Сначала дешевле',
    'price_desc'    => 'Сначала дороже',
    'popularity'    => 'По популярности',
    'rating'        => 'По рейтингу',
    'name_asc'      => 'По названию (А-Я)',
    'material'      => 'По материалу',
    'size'          => 'По размеру',
    'availability'  => 'По наличию',
];
$currentSort  = htmlspecialcharsbx($_GET['sort'] ?? 'date_desc');

// ── Следующая страница для пагинации ─────────────────────────────────────────
$nextPageNum  = $currentPage + 1;
$nextPageUrl  = $hasNextPage
    ? $APPLICATION->GetCurPage(false) . '?' . $navParam . '=' . $nextPageNum
    : '';
// Добавляем существующие GET-параметры кроме параметра пагинации
$baseParams   = $_GET;
unset($baseParams[$navParam]);
if (!empty($baseParams)) {
    $nextPageUrl  = $hasNextPage
        ? $APPLICATION->GetCurPage(false) . '?' . http_build_query($baseParams) . '&' . $navParam . '=' . $nextPageNum
        : '';
}
?>

<!-- ════════════════════════════════════════════════════════════════════════════
     HERO-секция раздела
     ════════════════════════════════════════════════════════════════════════════ -->
<section class="catalog-hero">
    <div class="catalog-hero__container container">
        <!-- Хлебные крошки — выводятся компонентом цепочки из Битрикс выше по стеку -->
        <nav class="breadcrumbs" aria-label="Вы здесь">
            <?php
            /*
             * $APPLICATION->GetNavChain() — стандартная цепочка разделов.
             * Вместо явного вывода цепочки используем SetPageProperty,
             * чтобы шаблон был независим от способа вывода хлебных крошек.
             */
            $chain = $APPLICATION->GetNavChain(false, false, true);
            if (!empty($chain)):
            ?>
            <ol class="breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">
                <li class="breadcrumbs__item" itemprop="itemListElement"
                    itemscope itemtype="https://schema.org/ListItem">
                    <a href="/" class="breadcrumbs__link" itemprop="item">
                        <span itemprop="name">Главная</span>
                    </a>
                    <meta itemprop="position" content="1">
                </li>
                <?php $pos = 2; foreach ($chain as $crumb): ?>
                <li class="breadcrumbs__item" itemprop="itemListElement"
                    itemscope itemtype="https://schema.org/ListItem">
                    <?php if (!empty($crumb['LINK'])): ?>
                    <a href="<?= htmlspecialcharsbx($crumb['LINK']) ?>"
                       class="breadcrumbs__link" itemprop="item">
                        <span itemprop="name"><?= htmlspecialchars($crumb['TITLE'], ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                    <?php else: ?>
                    <span class="breadcrumbs__current" itemprop="name">
                        <?= htmlspecialchars($crumb['TITLE'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <?php endif; ?>
                    <meta itemprop="position" content="<?= $pos++ ?>">
                </li>
                <?php endforeach; ?>
            </ol>
            <?php endif; ?>
        </nav>

        <!-- Заголовок раздела (единственный H1 на странице — ТЗ п.8.1) -->
        <h1 class="catalog-hero__title" itemprop="name"><?= $sectionName ?></h1>

        <?php if ($sectionDesc !== '' && $isFirstPage): ?>
        <p class="catalog-hero__desc"><?= $sectionDesc ?></p>
        <?php endif; ?>

    </div>
</section>

<!-- ════════════════════════════════════════════════════════════════════════════
     ОСНОВНОЙ БЛОК: боковой фильтр + контент
     ════════════════════════════════════════════════════════════════════════════ -->
<div class="catalog-layout container">

    <!-- ══ SIDEBAR — фильтр ════════════════════════════════════════════════ -->
    <aside class="catalog-layout__filter" id="catalog-filter-sidebar" aria-label="Фильтры товаров">

        <?php
        /*
         * Стандартный Битрикс smart-фильтр.
         * FILTER_NAME — глобальная переменная фильтрации (arFilter_catalog).
         * Шаблон 'chokerz' — кастомный вывод цветовых свотчей.
         */
        $APPLICATION->IncludeComponent(
            'bitrix:catalog.smart.filter',
            'chokerz',
            [
                'IBLOCK_TYPE'           => 'catalog',
                'IBLOCK_ID'             => (int)($arParams['IBLOCK_ID'] ?? 0),
                'SECTION_ID'            => (int)($sectionData['ID'] ?? 0),
                'SECTION_CODE'          => $sectionData['CODE'] ?? '',
                'SECTION_URL'           => $arParams['SECTION_URL'] ?? '#SECTION_CODE#/',
                'FILTER_NAME'           => 'arFilter_catalog',
                'PRICE_CODE'            => ['BASE'],
                'HIDE_NOT_AVAILABLE'    => 'N',
                'HIDE_NOT_AVAILABLE_OFFERS' => 'N',
                'PROPERTY_CODE'         => ['COLOR', 'MATERIAL', 'SIZE', 'TYPE', 'PURPOSE'],
                'PRICE_FILTER'          => 'Y',
                'SHOW_INTERVAL'         => 'SLIDER',
                'CACHE_TYPE'            => 'A',
                'CACHE_TIME'            => '3600',
            ],
            $component
        );
        ?>

    </aside>

    <!-- ══ КОНТЕНТ ═════════════════════════════════════════════════════════ -->
    <div class="catalog-layout__content">

        <!-- Строка управления: сортировка + кол-во товаров -->
        <div class="catalog-controls" data-catalog-controls>

            <p class="catalog-controls__count">
                <?php if ($totalItems > 0): ?>
                <span data-items-count><?= $totalItems ?></span>&nbsp;товаров
                <?php else: ?>
                Нет товаров
                <?php endif; ?>
            </p>

            <!-- Сортировка -->
            <form class="catalog-controls__sort-form"
                  method="get"
                  action="<?= htmlspecialcharsbx($APPLICATION->GetCurPage(false)) ?>"
                  data-sort-form>
                <?php
                // Передаём параметры фильтра как hidden-поля
                foreach ($baseParams as $key => $val):
                    if ($key === 'sort') continue;
                    if (is_array($val)):
                        foreach ($val as $v):
                ?>
                <input type="hidden" name="<?= htmlspecialcharsbx($key) ?>[]"
                       value="<?= htmlspecialcharsbx($v) ?>">
                <?php
                        endforeach;
                    else:
                ?>
                <input type="hidden" name="<?= htmlspecialcharsbx($key) ?>"
                       value="<?= htmlspecialcharsbx($val) ?>">
                <?php
                    endif;
                endforeach;
                ?>
                <label class="catalog-controls__sort-label" for="catalog-sort">
                    <!-- SVG: сортировка -->
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         aria-hidden="true" focusable="false">
                        <path d="M3 6h18M7 12h10M11 18h2"/>
                    </svg>
                    Сортировка:
                </label>
                <select id="catalog-sort"
                        name="sort"
                        class="catalog-controls__sort-select"
                        data-sort-select
                        aria-label="Выбор сортировки">
                    <?php foreach ($sortOptions as $val => $label): ?>
                    <option value="<?= $val ?>"<?= $currentSort === $val ? ' selected' : '' ?>>
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <!-- Переключатель вида (сетка / список) — desktop only -->
            <div class="catalog-controls__view" role="group" aria-label="Вид отображения">
                <button type="button"
                        class="catalog-controls__view-btn catalog-controls__view-btn--grid catalog-controls__view-btn--active"
                        data-view="grid"
                        aria-pressed="true"
                        aria-label="Сетка">
                    <!-- SVG: grid -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2"
                         aria-hidden="true" focusable="false">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                </button>
                <button type="button"
                        class="catalog-controls__view-btn catalog-controls__view-btn--list"
                        data-view="list"
                        aria-pressed="false"
                        aria-label="Список">
                    <!-- SVG: list -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2"
                         aria-hidden="true" focusable="false">
                        <line x1="8" y1="6" x2="21" y2="6"/>
                        <line x1="8" y1="12" x2="21" y2="12"/>
                        <line x1="8" y1="18" x2="21" y2="18"/>
                        <line x1="3" y1="6" x2="3.01" y2="6"/>
                        <line x1="3" y1="12" x2="3.01" y2="12"/>
                        <line x1="3" y1="18" x2="3.01" y2="18"/>
                    </svg>
                </button>
            </div>

        </div>

        <!-- ── СЕТКА ТОВАРОВ ──────────────────────────────────────────────── -->
        <div class="catalog-grid" id="catalog-grid" data-catalog-grid aria-live="polite" aria-label="Список товаров">

            <?php if (!empty($arResult['ITEMS'])): ?>
            <?php foreach ($arResult['ITEMS'] as $arItem):
                $itemId    = (int)$arItem['ID'];
                $itemName  = htmlspecialchars($arItem['NAME'], ENT_QUOTES, 'UTF-8');
                $itemUrl   = htmlspecialcharsbx($arItem['DETAIL_PAGE_URL'] ?? '#');
                $itemImg   = $arItem['PREVIEW_PICTURE']['SRC'] ?? '';
                $itemImgAlt= $arItem['PREVIEW_PICTURE']['ALT'] ?? $itemName;
                $itemPrice = $arItem['PRICES']['BASE']['PRICE'] ?? null;
                $itemOldPrice  = $arItem['PRICES']['BASE']['FULL_PRICE'] ?? null;
                $itemPriceFmt  = $itemPrice !== null
                    ? number_format((float)$itemPrice, 0, '.', '&nbsp;') . '&nbsp;₽'
                    : '';
                $itemOldPriceFmt = ($itemOldPrice && $itemOldPrice > $itemPrice)
                    ? number_format((float)$itemOldPrice, 0, '.', '&nbsp;') . '&nbsp;₽'
                    : '';
                $isAvail   = ($arItem['CATALOG_AVAILABLE'] ?? 'N') === 'Y';
                $isHit     = ($arItem['PROPERTIES']['HIT']['VALUE']  ?? '') === 'Y';
                $isNew     = ($arItem['PROPERTIES']['NEW']['VALUE']  ?? '') === 'Y';
                $isSale    = ($arItem['PROPERTIES']['SALE']['VALUE'] ?? '') === 'Y';
                $colorHex  = '';
                $colorRaw  = $arItem['PROPERTIES']['COLOR']['VALUE_XML_ID'] ?? '';
                if ($colorRaw !== '') {
                    $colorHex = ($colorRaw[0] !== '#') ? '#' . $colorRaw : $colorRaw;
                    if (!preg_match('/^#[0-9A-Fa-f]{3,8}$/', $colorHex)) {
                        $colorHex = '';
                    }
                }
                $colorLabel = htmlspecialchars(
                    $arItem['PROPERTIES']['COLOR']['VALUE'] ?? '', ENT_QUOTES, 'UTF-8'
                );
                $ratingVal = (float)($arItem['PROPERTIES']['RATING']['VALUE'] ?? 0);
            ?>

            <!-- Карточка товара -->
            <article class="product-card<?= !$isAvail ? ' product-card--unavailable' : '' ?>"
                     data-product-id="<?= $itemId ?>">

                <!-- Бейджи -->
                <?php if ($isHit || $isNew || $isSale): ?>
                <div class="product-card__badges" aria-label="Метки товара">
                    <?php if ($isHit):  ?><span class="badge badge--hit">Хит</span><?php endif; ?>
                    <?php if ($isNew):  ?><span class="badge badge--new">Новинка</span><?php endif; ?>
                    <?php if ($isSale): ?><span class="badge badge--sale">Акция</span><?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Изображение -->
                <a href="<?= $itemUrl ?>" class="product-card__img-link" tabindex="-1" aria-hidden="true">
                    <?php if ($itemImg !== ''): ?>
                    <img src="<?= htmlspecialcharsbx($itemImg) ?>"
                         alt="<?= htmlspecialchars($itemImgAlt, ENT_QUOTES, 'UTF-8') ?>"
                         title="<?= $itemName ?>"
                         class="product-card__img"
                         loading="lazy"
                         width="320"
                         height="320">
                    <?php else: ?>
                    <div class="product-card__img-placeholder" aria-hidden="true">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.5"
                             aria-hidden="true" focusable="false">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M3 15l4.5-4.5 3 3 4-4 6.5 6.5"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                        </svg>
                    </div>
                    <?php endif; ?>
                </a>

                <!-- Кнопка wishlist (overlay на изображении) -->
                <button type="button"
                        class="product-card__wishlist"
                        data-action="wishlist"
                        data-product-id="<?= $itemId ?>"
                        aria-label="Добавить в избранное: <?= $itemName ?>"
                        aria-pressed="false">
                    <!-- SVG: сердце -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         aria-hidden="true" focusable="false">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                </button>

                <!-- Тело карточки -->
                <div class="product-card__body">

                    <!-- Рейтинг -->
                    <?php if ($ratingVal > 0): ?>
                    <div class="product-card__rating rating" aria-label="Рейтинг: <?= $ratingVal ?> из 5">
                        <div class="rating__stars" aria-hidden="true">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="rating__star<?= $i <= round($ratingVal) ? ' rating__star--filled' : '' ?>">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2"
                                     aria-hidden="true" focusable="false">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                            </span>
                            <?php endfor; ?>
                        </div>
                        <span class="rating__val"><?= number_format($ratingVal, 1) ?></span>
                    </div>
                    <?php endif; ?>

                    <!-- Название -->
                    <h2 class="product-card__title">
                        <a href="<?= $itemUrl ?>" class="product-card__title-link">
                            <?= $itemName ?>
                        </a>
                    </h2>

                    <!-- Цветовой свотч -->
                    <?php if ($colorHex !== ''): ?>
                    <div class="product-card__color-row" aria-label="Цвет: <?= $colorLabel ?>">
                        <span class="product-card__color-dot"
                              style="background-color:<?= $colorHex ?>"
                              title="<?= $colorLabel ?>"></span>
                        <?php if ($colorLabel !== ''): ?>
                        <span class="product-card__color-label"><?= $colorLabel ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Цена + кнопка -->
                    <div class="product-card__footer">
                        <div class="product-card__price-block">
                            <?php if ($itemPriceFmt !== ''): ?>
                            <span class="product-card__price"><?= $itemPriceFmt ?></span>
                            <?php if ($itemOldPriceFmt !== ''): ?>
                            <span class="product-card__price-old"
                                  aria-label="Старая цена"><?= $itemOldPriceFmt ?></span>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="product-card__price product-card__price--empty">По запросу</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($isAvail): ?>
                        <button type="button"
                                class="btn btn--primary btn--sm product-card__btn"
                                data-action="add-to-cart"
                                data-product-id="<?= $itemId ?>"
                                data-product-name="<?= $itemName ?>"
                                data-product-price="<?= (float)$itemPrice ?>"
                                aria-label="В корзину: <?= $itemName ?>">
                            В корзину
                        </button>
                        <?php else: ?>
                        <span class="product-card__unavailable-label" aria-live="polite">
                            Нет в наличии
                        </span>
                        <?php endif; ?>
                    </div>

                </div>
            </article>

            <?php endforeach; ?>
            <?php else: ?>

            <!-- Пустой каталог -->
            <div class="catalog-empty" role="status" aria-live="polite">
                <!-- SVG: пустой результат -->
                <svg class="catalog-empty__icon" width="64" height="64" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.5"
                     aria-hidden="true" focusable="false">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M8 12h8M12 8v8"/>
                </svg>
                <p class="catalog-empty__text">Товары не найдены.</p>
                <a href="<?= htmlspecialcharsbx($APPLICATION->GetCurPage(false)) ?>"
                   class="btn btn--outline catalog-empty__reset">
                    Сбросить фильтры
                </a>
            </div>

            <?php endif; ?>

        </div>
        <!-- /catalog-grid -->

        <!-- ── ПАГИНАЦИЯ ──────────────────────────────────────────────────── -->
        <?php if ($totalPages > 1): ?>
        <nav class="catalog-pagination" aria-label="Страницы каталога" data-catalog-pagination>

            <!--
                «Загрузить ещё» — кнопка с обязательным <a href> внутри (ТЗ п.8.2).
                JS перехватывает клик и подгружает товары через AJAX без перехода.
                Если JS не загрузился — ссылка работает как обычный переход.
            -->
            <?php if ($hasNextPage): ?>
            <div class="catalog-pagination__loadmore" data-loadmore-wrap>
                <a href="<?= htmlspecialcharsbx($nextPageUrl) ?>"
                   class="btn btn--outline btn--load-more"
                   data-loadmore
                   data-next-page="<?= $nextPageNum ?>"
                   data-total-pages="<?= $totalPages ?>"
                   rel="next"
                   aria-label="Загрузить ещё товары (страница <?= $nextPageNum ?>)">
                    Загрузить ещё
                    <span class="btn__load-more-count" data-loadmore-remaining>
                        (ещё <?= $totalItems - $itemsOnPage * $currentPage ?> товаров)
                    </span>
                </a>
                <span class="catalog-pagination__progress" aria-hidden="true">
                    Показано <?= $itemsOnPage * $currentPage ?> из <?= $totalItems ?>
                </span>
            </div>
            <?php endif; ?>

            <!--
                Классическая пагинация через <a href> — обязательна для SEO (ТЗ п.8.2).
                Отображается только если JS отключён или прокрутка до конца.
                CSS: скрыта по умолчанию, JS отображает при необходимости.
            -->
            <ol class="catalog-pagination__pages" aria-label="Навигация по страницам"
                data-pages-list>
                <?php for ($p = 1; $p <= $totalPages; $p++):
                    $pageParams = $baseParams;
                    $pageParams[$navParam] = $p;
                    $pageUrl = $APPLICATION->GetCurPage(false) . '?' . http_build_query($pageParams);
                    // Страница 1 → URL без параметра пагинации (ТЗ п.8.2 — canonical)
                    if ($p === 1) {
                        $cleanParams = $baseParams;
                        unset($cleanParams[$navParam]);
                        $pageUrl = $APPLICATION->GetCurPage(false)
                            . (!empty($cleanParams) ? '?' . http_build_query($cleanParams) : '');
                    }
                ?>
                <li class="catalog-pagination__page-item">
                    <a href="<?= htmlspecialcharsbx($pageUrl) ?>"
                       class="catalog-pagination__page<?= $p === $currentPage ? ' catalog-pagination__page--active' : '' ?>"
                       <?= $p === $currentPage ? 'aria-current="page"' : '' ?>
                       rel="<?= $p === $currentPage - 1 ? 'prev' : ($p === $currentPage + 1 ? 'next' : '') ?>">
                        <?= $p ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ol>

        </nav>
        <?php endif; ?>

    </div>
    <!-- /catalog-layout__content -->

</div>
<!-- /catalog-layout -->

<!-- ════════════════════════════════════════════════════════════════════════════
     SEO-ТЕКСТ — только на первой странице (ТЗ п.8.2)
     ════════════════════════════════════════════════════════════════════════════ -->
<?php if ($isFirstPage && !empty($sectionData['DETAIL_TEXT'])): ?>
<section class="catalog-seo container" aria-label="Описание раздела">
    <div class="catalog-seo__content content-text">
        <?= $sectionData['DETAIL_TEXT'] ?>
    </div>
</section>
<?php endif; ?>
