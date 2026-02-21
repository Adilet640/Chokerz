<?php
/**
 * Шаблон компонента карточки товара (кастомный)
 * 
 * @author VibePilot
 * @version 1.0
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$element = $arResult['ELEMENT'];
?>

<article class="product-card" data-product-id="<?= $element['ID'] ?>">
    <!-- Бейджи -->
    <?php if ($element['PROPERTY_HIT_VALUE'] == 'Y' || $element['PROPERTY_NEW_VALUE'] == 'Y' || $element['PROPERTY_SALE_VALUE'] == 'Y'): ?>
    <div class="product-card__badges">
        <?php if ($element['PROPERTY_HIT_VALUE'] == 'Y'): ?>
            <span class="product-card__badge product-card__badge--hit">Хит</span>
        <?php endif; ?>
        
        <?php if ($element['PROPERTY_NEW_VALUE'] == 'Y'): ?>
            <span class="product-card__badge product-card__badge--new">Новинка</span>
        <?php endif; ?>
        
        <?php if ($element['PROPERTY_SALE_VALUE'] == 'Y'): ?>
            <span class="product-card__badge product-card__badge--sale">Акция</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Изображение товара -->
    <div class="product-card__image-wrapper">
        <a href="<?= htmlspecialchars($element['DETAIL_PAGE_URL']) ?>" class="product-card__image-link">
            <?php if ($element['PREVIEW_PICTURE_FILE']): ?>
                <img 
                    src="<?= htmlspecialchars($element['PREVIEW_PICTURE_FILE']) ?>" 
                    alt="<?= htmlspecialchars($element['NAME']) ?>"
                    class="product-card__image"
                    loading="lazy"
                >
            <?php else: ?>
                <div class="product-card__image-placeholder">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
                        <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            <?php endif; ?>
        </a>

        <!-- Кнопки действий -->
        <div class="product-card__actions">
            <button class="product-card__action-btn" data-action="wishlist" title="В избранное">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <button class="product-card__action-btn" data-action="quick-view" title="Быстрый просмотр">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Информация о товаре -->
    <div class="product-card__info">
        <!-- Название -->
        <h3 class="product-card__title">
            <a href="<?= htmlspecialchars($element['DETAIL_PAGE_URL']) ?>">
                <?= htmlspecialchars($element['NAME']) ?>
            </a>
        </h3>

        <!-- Артикул -->
        <?php if ($element['PROPERTY_ARTICLE_VALUE']): ?>
        <p class="product-card__article">
            Арт. <?= htmlspecialchars($element['PROPERTY_ARTICLE_VALUE']) ?>
        </p>
        <?php endif; ?>

        <!-- Характеристики -->
        <div class="product-card__specs">
            <?php if ($element['PROPERTY_TYPE_VALUE']): ?>
            <span class="product-card__spec"><?= htmlspecialchars($element['PROPERTY_TYPE_VALUE']) ?></span>
            <?php endif; ?>
            
            <?php if ($element['PROPERTY_MATERIAL_VALUE']): ?>
            <span class="product-card__spec"><?= htmlspecialchars($element['PROPERTY_MATERIAL_VALUE']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Цвета (если есть варианты) -->
        <?php if ($element['PROPERTY_COLOR_VALUE']): ?>
        <div class="product-card__colors">
            <span class="product-card__color" style="background-color: <?= htmlspecialchars($this->getColorHex($element['PROPERTY_COLOR_VALUE'])) ?>"></span>
        </div>
        <?php endif; ?>

        <!-- Цена и наличие -->
        <div class="product-card__bottom">
            <div class="product-card__price-block">
                <?php if ($element['FORMATTED_PRICE']): ?>
                <span class="product-card__price"><?= $element['FORMATTED_PRICE'] ?></span>
                <?php else: ?>
                <span class="product-card__price product-card__price--empty">Цена по запросу</span>
                <?php endif; ?>
            </div>

            <!-- Кнопка "В корзину" -->
            <?php if ($element['CATALOG_QUANTITY'] > 0): ?>
            <button class="btn btn--primary btn--sm" data-action="add-to-cart" data-product-id="<?= $element['ID'] ?>">
                В корзину
            </button>
            <?php else: ?>
            <button class="btn btn--secondary btn--sm" disabled>
                Нет в наличии
            </button>
            <?php endif; ?>
        </div>

        <!-- Ссылки на маркетплейсы -->
        <div class="product-card__marketplaces">
            <?php if ($element['PROPERTY_OZON_LINK_VALUE']): ?>
            <a href="<?= htmlspecialchars($element['PROPERTY_OZON_LINK_VALUE']) ?>" target="_blank" rel="noopener" class="product-card__marketplace-link ozon">
                Ozon
            </a>
            <?php endif; ?>
            
            <?php if ($element['PROPERTY_WB_LINK_VALUE']): ?>
            <a href="<?= htmlspecialchars($element['PROPERTY_WB_LINK_VALUE']) ?>" target="_blank" rel="noopener" class="product-card__marketplace-link wb">
                Wildberries
            </a>
            <?php endif; ?>
            
            <?php if ($element['PROPERTY_YM_LINK_VALUE']): ?>
            <a href="<?= htmlspecialchars($element['PROPERTY_YM_LINK_VALUE']) ?>" target="_blank" rel="noopener" class="product-card__marketplace-link ym">
                Яндекс.Маркет
            </a>
            <?php endif; ?>
        </div>
    </div>
</article>

<script>
// JavaScript для карточки товара
document.addEventListener('DOMContentLoaded', function() {
    const card = document.querySelector('.product-card[data-product-id="<?= $element['ID'] ?>"]');
    
    if (!card) return;

    // Добавление в избранное
    const wishlistBtn = card.querySelector('[data-action="wishlist"]');
    if (wishlistBtn) {
        wishlistBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = card.dataset.productId;
            
            // Вызов функции добавления в избранное
            if (typeof window.addToWishlist === 'function') {
                window.addToWishlist(productId);
            }
            
            this.classList.toggle('product-card__action-btn--active');
        });
    }

    // Быстрый просмотр
    const quickViewBtn = card.querySelector('[data-action="quick-view"]');
    if (quickViewBtn) {
        quickViewBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = card.dataset.productId;
            
            // Вызов модального окна быстрого просмотра
            if (typeof window.openQuickView === 'function') {
                window.openQuickView(productId);
            }
        });
    }

    // Добавление в корзину
    const addToCartBtn = card.querySelector('[data-action="add-to-cart"]');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = card.dataset.productId;
            
            // Вызов функции добавления в корзину
            if (typeof window.addToCart === 'function') {
                window.addToCart(productId, 1);
            }
        });
    }
});
</script>
