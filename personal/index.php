<?php
/**
 * Главная страница личного кабинета CHOKERZ
 * URL: /personal/
 * Шаблон: с двухколоночным layout (сайдбар + контент)
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;

$APPLICATION->SetTitle('Личный кабинет');
$APPLICATION->SetPageProperty('body_class', 'page-lk');
$APPLICATION->SetPageProperty('description', 'Личный кабинет CHOKERZ — управление заказами, избранным и профилем.');
?>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php'); ?>

<div class="lk-layout">
    <div class="lk-layout__container container">

        <?php
        // Сайдбар — навигация ЛК
        $APPLICATION->IncludeFile(
            SITE_TEMPLATE_PATH . '/includes/lk/sidebar.php',
            [],
            ['MODE' => 'php']
        );
        ?>

        <div class="lk-layout__content lk-content">

            <?php
            $APPLICATION->IncludeComponent(
                'custom:lk.dashboard',
                '.default',
                [
                    'IBLOCK_ID'             => \Bitrix\Main\Config\Option::get('chokerz', 'catalog_iblock_id', 0),
                    'WISHLIST_HL_CODE'      => 'Wishlist',
                    'WISHLIST_PREVIEW_COUNT' => 3,
                    'CACHE_TYPE'            => 'A',
                    'CACHE_TIME'            => 600,
                ],
                false
            );
            ?>

        </div><!-- /lk-layout__content -->

    </div><!-- /lk-layout__container -->
</div><!-- /lk-layout -->

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
