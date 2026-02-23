<?php
/**
 * component_epilog.php — catalog.item
 *
 * Выполняется автоматически после template.php.
 * Здесь: подключение ресурсов, вывод JSON-LD, инициализация цветовых свотчей.
 * Логика данных — в result_modifier.php, разметка — в template.php.
 *
 * Путь: local/components/custom/catalog.item/templates/.default/component_epilog.php
 *
 * @package CHOKERZ
 * @version 1.0
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Page\Asset;

// CSS компонента (Asset дедуплицирует повторные вызовы)
Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/styles/blocks/card.css');

// JS компонента с defer — никаких глобальных переменных (ТЗ п.6.1)
Asset::getInstance()->addJs(
    SITE_TEMPLATE_PATH . '/js/modules/catalog-item.js',
    false,
    ['defer' => true]
);
?>

<?php /* JSON-LD Schema.org — подготовлен в result_modifier.php */ ?>
<?php if (!empty($arResult['JSON_LD'])): ?>
<script type="application/ld+json"><?= $arResult['JSON_LD'] ?></script>
<?php endif; ?>

<script>
/**
 * Инициализация цветовых свотчей.
 * Применяет CSS custom property --color-swatch из data-color атрибута.
 * Без jQuery, без глобальных переменных (ТЗ п.6.1).
 */
(function initColorSwatches() {
    'use strict';

    document.querySelectorAll('.product-card__color-swatch[data-color]').forEach(function (swatch) {
        const hex = swatch.dataset.color;
        if (hex && /^#[0-9A-Fa-f]{3,8}$/.test(hex)) {
            swatch.style.setProperty('--color-swatch', hex);
            swatch.classList.add('product-card__color-swatch--loaded');
        }
    });
}());
</script>
