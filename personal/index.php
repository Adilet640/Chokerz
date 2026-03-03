<?php
/**
 * Главная страница личного кабинета CHOKERZ
 * URL: /personal/
 * Шаблон: двухколоночный layout (сайдбар + контент)
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Context;
use Bitrix\Main\Config\Option;

// Редирект неавторизованных — D7 API (CUser запрещён, п.10.2)
$currentUser = Context::getCurrent()->getUser();
if (!$currentUser || !$currentUser->isAuthorized()) {
    LocalRedirect(SITE_DIR . 'personal/login/?back_url=' . urlencode($_SERVER['REQUEST_URI']));
}

$APPLICATION->SetTitle('Личный кабинет');
$APPLICATION->SetPageProperty('description', 'Личный кабинет CHOKERZ — управление заказами, избранным и профилем.');
$APPLICATION->SetPageProperty('body_class', 'page-lk');
?>

<div class="lk-layout">
    <div class="lk-layout__container container">

        <?php
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
                    'IBLOCK_ID'              => Option::get('chokerz', 'catalog_iblock_id', 0),
                    'WISHLIST_HL_CODE'       => 'Wishlist',
                    'WISHLIST_PREVIEW_COUNT' => 3,
                    'CACHE_TYPE'             => 'A',
                    'CACHE_TIME'             => 600,
                ],
                false
            );
            ?>

        </div><!-- /lk-layout__content -->

    </div><!-- /lk-layout__container -->
</div><!-- /lk-layout -->
