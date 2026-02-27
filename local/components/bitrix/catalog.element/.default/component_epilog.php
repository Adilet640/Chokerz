<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Page\Asset;

$asset = Asset::getInstance();

// CSS страницы товара
$asset->addCss(SITE_TEMPLATE_PATH . '/styles/blocks/product-detail.css');

// JS модуль детальной страницы (defer — ТЗ п.5.1 и п.6.1)
$asset->addJs(SITE_TEMPLATE_PATH . '/js/modules/product-detail.js', false, ['defer' => true]);
?>

<?php /* JSON-LD Schema.org Product — подготовлен в result_modifier.php */ ?>
<?php if (!empty($arResult['JSON_LD'])): ?>
<script type="application/ld+json"><?= $arResult['JSON_LD'] ?></script>
<?php endif; ?>
