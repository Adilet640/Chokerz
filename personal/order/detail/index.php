<?php
/**
 * Страница детали заказа ЛК
 * URL: /personal/order/detail/#ID#/
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $USER;
if (!$USER->IsAuthorized()) {
    LocalRedirect(SITE_DIR . 'personal/login/?back_url=' . urlencode($_SERVER['REQUEST_URI']));
}

$APPLICATION->SetTitle('Заказ');
$APPLICATION->SetPageProperty('body_class', 'page-lk page-lk-order-detail');
?>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php'); ?>

<div class="lk-layout">
    <div class="lk-layout__container container">

        <?php $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/includes/lk/sidebar.php', [], ['MODE' => 'php']); ?>

        <div class="lk-layout__content lk-content">
            <?php
            $APPLICATION->IncludeComponent(
                'bitrix:sale.personal.order.detail',
                '.default',
                [
                    'PATH_TO_LIST'  => '/personal/order/list/',
                    'CACHE_TYPE'    => 'N',
                    'SET_TITLE'     => 'N',
                ],
                false
            );
            ?>
        </div>

    </div>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
