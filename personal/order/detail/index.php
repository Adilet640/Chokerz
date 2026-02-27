<?php
/**
 * Страница списка заказов ЛК
 * URL: /personal/order/list/
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $USER;
if (!$USER->IsAuthorized()) {
    LocalRedirect(SITE_DIR . 'personal/login/?back_url=' . urlencode($_SERVER['REQUEST_URI']));
}

$APPLICATION->SetTitle('Мои заказы');
$APPLICATION->SetPageProperty('body_class', 'page-lk page-lk-orders');
$APPLICATION->SetPageProperty('description', 'История заказов CHOKERZ');
?>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php'); ?>

<div class="lk-layout">
    <div class="lk-layout__container container">

        <?php $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/includes/lk/sidebar.php', [], ['MODE' => 'php']); ?>

        <div class="lk-layout__content lk-content">
            <?php
            $APPLICATION->IncludeComponent(
                'bitrix:sale.personal.order.list',
                '.default',
                [
                    'PATH_TO_DETAIL'    => '/personal/order/detail/#ID#/',
                    'SORT_BY'           => 'DATE_INSERT',
                    'SORT_ORDER'        => 'DESC',
                    'CACHE_TYPE'        => 'N', // Не кешировать — список заказов должен быть актуальным
                    'COUNT_ELEMENTS'    => 20,
                    'SET_TITLE'         => 'N',
                    'SET_BROWSER_TITLE' => 'N',
                ],
                false
            );
            ?>
        </div>

    </div>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
