<?php
/**
 * Карточка товара в результатах поиска CHOKERZ
 * Переменная $product обязательна в области видимости
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $product */
$price = number_format((float)($product['PRICE'] ?? 0), 0, '.', ' ');
?>
<div class="search-product-card<?= $product['IS_HIT'] ? ' search-product-card--hit' : '' ?><?= $product['IS_NEW'] ? ' search-product-card--new' : '' ?>">

    <a href="<?= htmlspecialcharsEx($product['DETAIL_URL']) ?>" class="search-product-card__img-wrap" tabindex="-1">
        <?php if (!empty($product['PREVIEW_SRC'])): ?>
        <img
            src="<?= htmlspecialcharsEx($product['PREVIEW_SRC']) ?>"
            alt="<?= htmlspecialcharsEx($product['NAME']) ?>"
            class="search-product-card__img"
            loading="lazy"
            width="280"
            height="280"
        >
        <?php endif; ?>

        <?php if ($product['IS_HIT']): ?>
        <span class="search-product-card__badge search-product-card__badge--hit" aria-label="Хит">Хит</span>
        <?php elseif ($product['IS_NEW']): ?>
        <span class="search-product-card__badge search-product-card__badge--new" aria-label="Новинка">Новинка</span>
        <?php endif; ?>
    </a>

    <div class="search-product-card__body">
        <a href="<?= htmlspecialcharsEx($product['DETAIL_URL']) ?>" class="search-product-card__name">
            <?= htmlspecialcharsEx($product['NAME']) ?>
        </a>

        <?php if (!empty($product['ARTICLE'])): ?>
        <span class="search-product-card__article">Арт. <?= htmlspecialcharsEx($product['ARTICLE']) ?></span>
        <?php endif; ?>

        <?php if ((float)$product['PRICE'] > 0): ?>
        <div class="search-product-card__price">
            <?= $price ?> ₽
        </div>
        <?php endif; ?>

        <div class="search-product-card__actions">
            <button
                type="button"
                class="btn btn--primary search-product-card__buy"
                data-add-to-cart="<?= (int)$product['ID'] ?>"
                aria-label="Добавить в корзину"
            >В корзину</button>
            <button
                type="button"
                class="search-product-card__wishlist"
                data-wishlist-toggle="<?= (int)$product['ID'] ?>"
                aria-label="В избранное"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </button>
        </div>
    </div>

</div>
