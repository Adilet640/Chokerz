<?php
/**
 * Шаблон компонента карточки товара CHOKERZ
 *
 * Изменения (2026-02-23):
 *  - Удалён inline-скрипт <script> (нарушение ТЗ п.6.1) → перенесён в component_epilog.php
 *  - Удалён вызов $this->getColorHex() → цвет берётся из $props['COLOR']['HEX'] (result_modifier)
 *  - Флаги бейджей и наличия берутся из $arResult (подготовлены в result_modifier.php)
 *  - OFFERS_JSON передаётся через data-атрибут для JS
 *
 * Путь: local/components/custom/catalog.item/templates/.default/template.php
 *
 * @package CHOKERZ
 * @version 1.1
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$element     = $arResult['ELEMENT'];
$offers      = $arResult['OFFERS'];
$offersJson  = $arResult['OFFERS_JSON'] ?? '[]';

$productId   = (int)$element['ID'];
$productName = htmlspecialchars($element['NAME'], ENT_QUOTES, 'UTF-8');
$detailUrl   = htmlspecialchars($element['DETAIL_PAGE_URL'] ?? '#', ENT_QUOTES, 'UTF-8');
$isAvailable = $element['IS_AVAILABLE'];      // подготовлено в result_modifier.php
$badges      = $element['BADGES'];            // подготовлено в result_modifier.php
$props       = $element['PROPERTIES'] ?? [];
$colorHex    = $props['COLOR']['HEX'] ?? '';  // нормализован в result_modifier.php
?>

<article
    class="product-card"
    data-product-id="<?= $productId ?>"
    data-available="<?= $isAvailable ? 'true' : 'false' ?>"
    data-offers="<?= $offersJson ?>"
>
    <?php if ($element['HAS_BADGES']): ?>
    <div class="product-card__badges" aria-label="Метки товара">
        <?php if ($badges['HIT']): ?>
            <span class="product-card__badge product-card__badge--hit">Хит</span>
        <?php endif; ?>
        <?php if ($badges['NEW']): ?>
            <span class="product-card__badge product-card__badge--new">Новинка</span>
        <?php endif; ?>
        <?php if ($badges['SALE']): ?>
            <span class="product-card__badge product-card__badge--sale">Акция</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="product-card__image-wrapper">
        <a href="<?= $detailUrl ?>" class="product-card__image-link">
            <?php if (!empty($element['PREVIEW_PICTURE_SRC'])): ?>
                <img
                    src="<?= htmlspecialchars($element['PREVIEW_PICTURE_SRC'], ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= $productName ?>"
                    title="<?= $productName ?>"
                    class="product-card__image"
                    loading="lazy"
                    width="400"
                    height="400"
                >
            <?php else: ?>
                <div class="product-card__image-placeholder" aria-hidden="true">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            <?php endif; ?>
        </a>

        <div class="product-card__actions" role="group" aria-label="Действия с товаром">
            <button
                class="product-card__action-btn"
                data-action="wishlist"
                data-product-id="<?= $productId ?>"
                aria-label="Добавить в избранное"
                title="В избранное"
            >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <button
                class="product-card__action-btn"
                data-action="quick-view"
                data-product-id="<?= $productId ?>"
                aria-label="Быстрый просмотр"
                title="Быстрый просмотр"
            >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="product-card__info">
        <h3 class="product-card__title">
            <a href="<?= $detailUrl ?>"><?= $productName ?></a>
        </h3>

        <?php if (!empty($props['ARTICLE']['VALUE'])): ?>
        <p class="product-card__article">
            Арт.&nbsp;<?= htmlspecialchars($props['ARTICLE']['VALUE'], ENT_QUOTES, 'UTF-8') ?>
        </p>
        <?php endif; ?>

        <div class="product-card__specs">
            <?php if (!empty($props['TYPE']['VALUE'])): ?>
                <span class="product-card__spec">
                    <?= htmlspecialchars($props['TYPE']['VALUE'], ENT_QUOTES, 'UTF-8') ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($props['MATERIAL']['VALUE'])): ?>
                <span class="product-card__spec">
                    <?= htmlspecialchars($props['MATERIAL']['VALUE'], ENT_QUOTES, 'UTF-8') ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (!empty($props['COLOR']['VALUE'])): ?>
        <div class="product-card__colors" aria-label="Цвет: <?= htmlspecialchars($props['COLOR']['VALUE'], ENT_QUOTES, 'UTF-8') ?>">
            <span
                class="product-card__color-swatch"
                data-color="<?= htmlspecialchars($colorHex, ENT_QUOTES, 'UTF-8') ?>"
                title="<?= htmlspecialchars($props['COLOR']['VALUE'], ENT_QUOTES, 'UTF-8') ?>"
            ></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($offers)): ?>
        <div class="product-card__offers" role="group" aria-label="Размеры">
            <?php foreach ($offers as $offer): ?>
                <?php if (!empty($offer['SIZE'])): ?>
                <button
                    class="product-card__size-btn"
                    data-offer-id="<?= (int)$offer['ID'] ?>"
                    data-size="<?= htmlspecialchars($offer['SIZE'], ENT_QUOTES, 'UTF-8') ?>"
                    <?= !$offer['IS_AVAILABLE'] ? 'disabled aria-disabled="true"' : '' ?>
                >
                    <?= htmlspecialchars($offer['SIZE'], ENT_QUOTES, 'UTF-8') ?>
                </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="product-card__bottom">
            <div class="product-card__price-block">
                <?php if (!empty($element['FORMATTED_PRICE'])): ?>
                    <span class="product-card__price"><?= $element['FORMATTED_PRICE'] ?></span>
                <?php else: ?>
                    <span class="product-card__price product-card__price--empty">Цена по запросу</span>
                <?php endif; ?>
            </div>

            <?php if ($isAvailable): ?>
            <button
                class="btn btn--primary btn--sm"
                data-action="add-to-cart"
                data-product-id="<?= $productId ?>"
                data-product-name="<?= $productName ?>"
                data-product-price="<?= (float)($element['PRICE'] ?? 0) ?>"
                aria-label="Добавить «<?= $productName ?>» в корзину"
            >
                В корзину
            </button>
            <?php else: ?>
            <button class="btn btn--secondary btn--sm" disabled aria-disabled="true">
                Нет в наличии
            </button>
            <?php endif; ?>
        </div>

        <?php if ($element['HAS_MARKETPLACE_LINKS']): ?>
        <div class="product-card__marketplaces">
            <?php
            $marketplaces = [
                'OZON_LINK' => ['label' => 'Купить на Ozon',           'mod' => 'ozon'],
                'WB_LINK'   => ['label' => 'Купить на Wildberries',    'mod' => 'wb'],
                'YM_LINK'   => ['label' => 'Купить на Яндекс Маркет', 'mod' => 'ym'],
            ];
            foreach ($marketplaces as $code => $mp):
                if (empty($props[$code]['VALUE'])) continue;
            ?>
                <a
                    href="<?= htmlspecialchars($props[$code]['VALUE'], ENT_QUOTES, 'UTF-8') ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="product-card__marketplace-link product-card__marketplace-link--<?= $mp['mod'] ?>"
                >
                    <?= $mp['label'] ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</article>
