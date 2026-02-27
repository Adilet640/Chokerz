<?php


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$productId   = (int)$arResult['ID'];
$productName = htmlspecialchars($arResult['NAME'] ?? '', ENT_QUOTES, 'UTF-8');
$previewText = $arResult['PREVIEW_TEXT'] ?? '';
$detailText  = $arResult['DETAIL_TEXT']  ?? '';
$detailUrl   = htmlspecialchars($arResult['DETAIL_PAGE_URL'] ?? '#', ENT_QUOTES, 'UTF-8');
$article     = htmlspecialchars($arResult['PROPERTIES']['ARTICLE']['VALUE'] ?? '', ENT_QUOTES, 'UTF-8');

$gallery        = $arResult['GALLERY']         ?? [];
$badges         = $arResult['BADGES']          ?? [];
$hasBadges      = $arResult['HAS_BADGES']      ?? false;
$isAvailable    = $arResult['IS_AVAILABLE']    ?? false;
$priceFormatted = $arResult['PRICE_FORMATTED'] ?? '';
$priceOldFormatted = $arResult['PRICE_OLD_FORMATTED'] ?? '';
$priceValue     = (float)($arResult['PRICE_VALUE'] ?? 0);
$colors         = $arResult['COLORS']          ?? [];
$sizes          = $arResult['SIZES']           ?? [];
$offersJson     = $arResult['OFFERS_JSON']     ?? '[]';
$specs          = $arResult['SPECS']           ?? [];
$mpLinks        = $arResult['MARKETPLACE_LINKS'] ?? [];
$hasMpLinks     = $arResult['HAS_MARKETPLACE_LINKS'] ?? false;
?>

<!-- ════════════════════════════════════════════════════════════════════════
     Детальная страница товара
     ════════════════════════════════════════════════════════════════════════ -->
<article
    class="product-detail"
    itemscope
    itemtype="https://schema.org/Product"
    data-product-id="<?= $productId ?>"
    data-offers="<?= $offersJson ?>"
    data-price="<?= $priceValue ?>"
>
    <div class="product-detail__layout container">

        <!-- ══ ГАЛЕРЕЯ ══════════════════════════════════════════════════════ -->
        <div class="product-detail__gallery gallery-block" id="product-gallery">

            <!-- Миниатюры (thumbnails) -->
            <?php if (count($gallery) > 1): ?>
            <div class="gallery-block__thumbs" role="list" aria-label="Миниатюры товара">
                <?php foreach ($gallery as $idx => $img): ?>
                <button
                    type="button"
                    class="gallery-block__thumb<?= $idx === 0 ? ' gallery-block__thumb--active' : '' ?>"
                    data-gallery-thumb="<?= $idx ?>"
                    aria-label="Фото <?= $idx + 1 ?>"
                    aria-pressed="<?= $idx === 0 ? 'true' : 'false' ?>"
                    role="listitem"
                >
                    <img
                        src="<?= htmlspecialcharsbx($img['SRC']) ?>"
                        alt="<?= $img['ALT'] ?> — фото <?= $idx + 1 ?>"
                        width="80"
                        height="80"
                        loading="lazy"
                        class="gallery-block__thumb-img"
                    >
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Основное изображение -->
            <div class="gallery-block__main" data-gallery-main>
                <?php if (!empty($gallery)): ?>
                <?php foreach ($gallery as $idx => $img): ?>
                <div
                    class="gallery-block__slide<?= $idx === 0 ? ' gallery-block__slide--active' : '' ?>"
                    data-gallery-slide="<?= $idx ?>"
                    aria-hidden="<?= $idx === 0 ? 'false' : 'true' ?>"
                >
                    <img
                        src="<?= htmlspecialcharsbx($img['SRC']) ?>"
                        alt="<?= $img['ALT'] ?>"
                        title="<?= $img['ALT'] ?>"
                        width="<?= $img['WIDTH'] ?>"
                        height="<?= $img['HEIGHT'] ?>"
                        <?= $idx === 0 ? '' : 'loading="lazy"' ?>
                        class="gallery-block__img"
                        itemprop="image"
                    >
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="gallery-block__placeholder" aria-hidden="true">
                    <!-- SVG: placeholder изображения -->
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.5" aria-hidden="true" focusable="false">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <path d="M3 15l4.5-4.5 3 3 4-4 6.5 6.5"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                    </svg>
                    <span>Фото скоро появится</span>
                </div>
                <?php endif; ?>
            </div>

        </div>
        <!-- /gallery-block -->

        <!-- ══ ИНФОРМАЦИОННАЯ ПАНЕЛЬ ═══════════════════════════════════════ -->
        <div class="product-detail__info" id="product-info">

            <!-- Бейджи -->
            <?php if ($hasBadges): ?>
            <div class="product-detail__badges" aria-label="Метки товара">
                <?php if ($badges['HIT']): ?>
                <span class="badge badge--hit">Хит</span>
                <?php endif; ?>
                <?php if ($badges['NEW']): ?>
                <span class="badge badge--new">Новинка</span>
                <?php endif; ?>
                <?php if ($badges['SALE']): ?>
                <span class="badge badge--sale">Акция</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Заголовок товара -->
            <h1 class="product-detail__title" itemprop="name"><?= $productName ?></h1>

            <!-- Артикул + рейтинг -->
            <div class="product-detail__meta">
                <?php if ($article !== ''): ?>
                <span class="product-detail__article">Арт.:&nbsp;<?= $article ?></span>
                <?php endif; ?>

                <!-- Рейтинг (значение управляется JS из данных отзывов) -->
                <div class="product-detail__rating rating"
                     data-rating-product="<?= $productId ?>"
                     aria-label="Рейтинг товара">
                    <div class="rating__stars" aria-hidden="true">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="rating__star" data-star="<?= $i ?>">
                            <!-- SVG: звезда -->
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2"
                                 aria-hidden="true" focusable="false">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                        </span>
                        <?php endfor; ?>
                    </div>
                    <a href="#product-reviews"
                       class="rating__count"
                       aria-label="Перейти к отзывам">
                        <span data-reviews-count>0</span>&nbsp;отзывов
                    </a>
                </div>
            </div>

            <!-- Цена -->
            <div class="product-detail__price-block" data-price-block itemprop="offers"
                 itemscope itemtype="https://schema.org/Offer">
                <meta itemprop="priceCurrency" content="RUB">
                <meta itemprop="availability"
                      content="<?= $isAvailable ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' ?>">

                <span class="product-detail__price" data-price-display itemprop="price"
                      content="<?= $priceValue ?>">
                    <?= $priceFormatted !== '' ? $priceFormatted : 'Цена по запросу' ?>
                </span>
                <?php if ($priceOldFormatted !== ''): ?>
                <span class="product-detail__price-old" data-price-old aria-label="Старая цена">
                    <?= $priceOldFormatted ?>
                </span>
                <?php endif; ?>
            </div>

            <!-- Выбор цвета -->
            <?php if (!empty($colors)): ?>
            <div class="product-detail__option-group">
                <span class="product-detail__option-label">
                    Цвет:&nbsp;<span class="product-detail__option-value" data-selected-color-label></span>
                </span>
                <div class="product-detail__colors" role="group" aria-label="Выбор цвета">
                    <?php $firstColor = true; ?>
                    <?php foreach ($colors as $hex => $label): ?>
                    <?php $colorLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    <?php $colorHex   = htmlspecialchars($hex,   ENT_QUOTES, 'UTF-8'); ?>
                    <button
                        type="button"
                        class="color-swatch<?= $firstColor ? ' color-swatch--active' : '' ?>"
                        data-color-hex="<?= $colorHex ?>"
                        data-color-label="<?= $colorLabel ?>"
                        aria-label="<?= $colorLabel ?>"
                        aria-pressed="<?= $firstColor ? 'true' : 'false' ?>"
                        title="<?= $colorLabel ?>"
                    >
                        <span class="color-swatch__dot" style="background-color:<?= $colorHex ?>"></span>
                    </button>
                    <?php $firstColor = false; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Выбор размера -->
            <?php if (!empty($sizes)): ?>
            <div class="product-detail__option-group">
                <div class="product-detail__size-head">
                    <span class="product-detail__option-label">
                        Размер:&nbsp;<span class="product-detail__option-value" data-selected-size-label></span>
                    </span>
                    <button type="button"
                            class="product-detail__size-guide-btn"
                            data-modal-trigger="size-guide"
                            aria-haspopup="dialog">
                        Таблица размеров
                    </button>
                </div>
                <div class="product-detail__sizes" role="group" aria-label="Выбор размера">
                    <?php foreach ($sizes as $sizeVal => $sizeAvail): ?>
                    <?php $sizeLabel = htmlspecialchars((string)$sizeVal, ENT_QUOTES, 'UTF-8'); ?>
                    <button
                        type="button"
                        class="size-btn<?= !$sizeAvail ? ' size-btn--unavailable' : '' ?>"
                        data-size="<?= $sizeLabel ?>"
                        <?= !$sizeAvail ? 'aria-disabled="true"' : '' ?>
                        aria-label="Размер <?= $sizeLabel ?><?= !$sizeAvail ? ' — нет в наличии' : '' ?>"
                    >
                        <?= $sizeLabel ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Количество + Добавить в корзину -->
            <div class="product-detail__buy-row">

                <!-- Счётчик количества -->
                <div class="qty-control" role="group" aria-label="Количество товара">
                    <button
                        type="button"
                        class="qty-control__btn qty-control__btn--minus"
                        data-qty-minus
                        aria-label="Уменьшить количество"
                    >
                        <!-- SVG: минус -->
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                             aria-hidden="true" focusable="false">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </button>
                    <input
                        type="number"
                        class="qty-control__input"
                        data-qty-input
                        value="1"
                        min="1"
                        max="99"
                        aria-label="Количество"
                        readonly
                    >
                    <button
                        type="button"
                        class="qty-control__btn qty-control__btn--plus"
                        data-qty-plus
                        aria-label="Увеличить количество"
                    >
                        <!-- SVG: плюс -->
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                             aria-hidden="true" focusable="false">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </button>
                </div>

                <!-- Кнопка в корзину -->
                <?php if ($isAvailable): ?>
                <button
                    type="button"
                    class="btn btn--primary btn--lg product-detail__add-to-cart"
                    data-action="add-to-cart"
                    data-product-id="<?= $productId ?>"
                    data-product-name="<?= $productName ?>"
                    data-product-price="<?= $priceValue ?>"
                    aria-label="Добавить «<?= $productName ?>» в корзину"
                >
                    <!-- SVG: корзина -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         aria-hidden="true" focusable="false">
                        <circle cx="9" cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    В корзину
                </button>
                <?php else: ?>
                <button type="button" class="btn btn--outline btn--lg" disabled aria-disabled="true">
                    Нет в наличии
                </button>
                <?php endif; ?>

                <!-- Кнопка "В избранное" -->
                <button
                    type="button"
                    class="btn btn--icon product-detail__wishlist"
                    data-action="wishlist"
                    data-product-id="<?= $productId ?>"
                    aria-label="Добавить в избранное"
                    aria-pressed="false"
                >
                    <!-- SVG: сердце -->
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         aria-hidden="true" focusable="false">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                </button>
            </div>

            <!-- Маркетплейс-кнопки -->
            <?php if ($hasMpLinks): ?>
            <div class="product-detail__marketplaces">
                <?php
                $mpConfig = [
                    'OZON' => ['label' => 'Купить на Ozon',           'mod' => 'ozon'],
                    'WB'   => ['label' => 'Купить на Wildberries',    'mod' => 'wb'],
                    'YM'   => ['label' => 'Купить на Яндекс Маркет', 'mod' => 'ym'],
                ];
                foreach ($mpConfig as $key => $cfg):
                    if (empty($mpLinks[$key])) continue;
                ?>
                <a
                    href="<?= htmlspecialcharsbx($mpLinks[$key]) ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn btn--mp btn--mp--<?= $cfg['mod'] ?>"
                    aria-label="<?= htmlspecialchars($cfg['label'], ENT_QUOTES, 'UTF-8') ?>"
                >
                    <?= htmlspecialchars($cfg['label'], ENT_QUOTES, 'UTF-8') ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Доставка (info-snippet) -->
            <div class="product-detail__delivery-info delivery-info">
                <div class="delivery-info__item">
                    <!-- SVG: грузовик -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         aria-hidden="true" focusable="false">
                        <rect x="1" y="3" width="15" height="13" rx="1"/>
                        <path d="M16 8h4l3 5v3h-7V8z"/>
                        <circle cx="5.5" cy="18.5" r="2.5"/>
                        <circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                    <span>Доставка СДЭК, Почта России, OZON</span>
                </div>
                <div class="delivery-info__item">
                    <!-- SVG: щит -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         aria-hidden="true" focusable="false">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <span>Возврат в течение 14 дней</span>
                </div>
            </div>

        </div>
        <!-- /product-detail__info -->

    </div>
    <!-- /product-detail__layout -->

    <!-- ══ ВКЛАДКИ: Описание / Характеристики / Отзывы ═══════════════════ -->
    <div class="product-tabs container" id="product-tabs" data-tabs>

        <!-- Навигация по вкладкам -->
        <div class="product-tabs__nav" role="tablist" aria-label="Информация о товаре">
            <button
                type="button"
                class="product-tabs__tab product-tabs__tab--active"
                role="tab"
                aria-selected="true"
                aria-controls="tab-description"
                id="tab-btn-description"
                data-tab="description"
            >
                Описание
            </button>
            <button
                type="button"
                class="product-tabs__tab"
                role="tab"
                aria-selected="false"
                aria-controls="tab-specs"
                id="tab-btn-specs"
                data-tab="specs"
            >
                Характеристики
            </button>
            <?php if (!empty($sizes)): ?>
            <button
                type="button"
                class="product-tabs__tab"
                role="tab"
                aria-selected="false"
                aria-controls="tab-size-guide"
                id="tab-btn-size-guide"
                data-tab="size-guide"
            >
                Таблица размеров
            </button>
            <?php endif; ?>
            <button
                type="button"
                class="product-tabs__tab"
                role="tab"
                aria-selected="false"
                aria-controls="tab-reviews"
                id="tab-btn-reviews"
                data-tab="reviews"
                data-tab-reviews
            >
                Отзывы&nbsp;<span class="product-tabs__tab-count" data-reviews-count-tab></span>
            </button>
        </div>

        <!-- Вкладка: Описание -->
        <div
            class="product-tabs__panel product-tabs__panel--active"
            role="tabpanel"
            id="tab-description"
            aria-labelledby="tab-btn-description"
        >
            <div class="product-tabs__text content-text" itemprop="description">
                <?php if ($detailText !== ''): ?>
                <?= $detailText ?>
                <?php elseif ($previewText !== ''): ?>
                <p><?= $previewText ?></p>
                <?php else: ?>
                <p class="product-tabs__empty">Описание не добавлено.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Вкладка: Характеристики -->
        <div
            class="product-tabs__panel"
            role="tabpanel"
            id="tab-specs"
            aria-labelledby="tab-btn-specs"
            hidden
        >
            <?php if (!empty($specs)): ?>
            <table class="specs-table" aria-label="Характеристики товара">
                <tbody>
                    <?php foreach ($specs as $spec): ?>
                    <tr class="specs-table__row">
                        <th class="specs-table__label" scope="row"><?= $spec['LABEL'] ?></th>
                        <td class="specs-table__value"><?= $spec['VALUE'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="product-tabs__empty">Характеристики не указаны.</p>
            <?php endif; ?>
        </div>

        <!-- Вкладка: Таблица размеров -->
        <?php if (!empty($sizes)): ?>
        <div
            class="product-tabs__panel"
            role="tabpanel"
            id="tab-size-guide"
            aria-labelledby="tab-btn-size-guide"
            hidden
        >
            <div class="size-guide">
                <p class="size-guide__note">
                    Для правильного выбора размера измерьте обхват шеи питомца
                    и сравните с таблицей ниже.
                </p>
                <table class="size-guide__table" aria-label="Таблица размеров ошейников">
                    <thead>
                        <tr>
                            <th scope="col">Размер</th>
                            <th scope="col">Обхват шеи, см</th>
                            <th scope="col">Порода (пример)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>XS</td><td>20–28</td><td>Той-терьер, Чихуахуа</td></tr>
                        <tr><td>S</td> <td>25–35</td><td>Шпиц, Такса</td></tr>
                        <tr><td>M</td> <td>30–45</td><td>Бигль, Кокер-спаниель</td></tr>
                        <tr><td>L</td> <td>40–55</td><td>Лабрадор, Хаски</td></tr>
                        <tr><td>XL</td><td>50–65</td><td>Немецкая овчарка, Ротвейлер</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Вкладка: Отзывы -->
        <div
            class="product-tabs__panel"
            role="tabpanel"
            id="tab-reviews"
            aria-labelledby="tab-btn-reviews"
            id="product-reviews"
            hidden
        >
            <div class="reviews-block" data-reviews-container data-product-id="<?= $productId ?>">

                <!-- Сводка рейтинга -->
                <div class="reviews-block__summary">
                    <div class="reviews-block__avg">
                        <span class="reviews-block__avg-value" data-reviews-avg>—</span>
                        <div class="rating__stars reviews-block__avg-stars" aria-hidden="true">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="rating__star" data-star="<?= $i ?>">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2"
                                     aria-hidden="true" focusable="false">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                            </span>
                            <?php endfor; ?>
                        </div>
                        <span class="reviews-block__count" data-reviews-count-full></span>
                    </div>
                </div>

                <!-- Список отзывов (заполняется JS из данных OZON / HL-блока) -->
                <ul class="reviews-block__list" data-reviews-list aria-live="polite" aria-label="Список отзывов">
                    <li class="reviews-block__loading" data-reviews-loader aria-live="polite">
                        Загрузка отзывов...
                    </li>
                </ul>

            </div>
        </div>

    </div>
    <!-- /product-tabs -->

    <!-- ══ ПОХОЖИЕ ТОВАРЫ ════════════════════════════════════════════════ -->
    <section class="product-related container" aria-labelledby="related-heading">
        <h2 class="product-related__title" id="related-heading">Похожие товары</h2>
        <div class="product-related__grid" data-related-grid>
            <?php
            /*
             * Подключаем компонент catalog.section в режиме связанных товаров.
             * SECTION_ID берётся из текущего элемента.
             * Исключаем текущий товар через FILTER_FIELD.
             */
            $APPLICATION->IncludeComponent(
                'bitrix:catalog',
                'chokerz.related',
                [
                    'IBLOCK_TYPE'        => 'catalog',
                    'IBLOCK_ID'          => $arResult['IBLOCK_ID'] ?? 0,
                    'SECTION_ID'         => $arResult['IBLOCK_SECTION_ID'] ?? 0,
                    'PAGE_ELEMENT_COUNT' => 4,
                    'ELEMENT_SORT_FIELD' => 'SORT',
                    'ELEMENT_SORT_ORDER' => 'ASC',
                    'FILTER_NAME'        => 'filter_related',
                    'CACHE_TYPE'         => 'A',
                    'CACHE_TIME'         => '3600',
                    'EXCLUDE_ELEMENT_ID' => $productId,
                ],
                $component,
                ['HIDE_ICONS' => 'Y']
            );
            ?>
        </div>
    </section>

</article>
<!-- /product-detail -->
