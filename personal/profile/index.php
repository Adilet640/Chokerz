<?php
/**
 * Страница профиля ЛК
 * URL: /personal/profile/
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $USER;
if (!$USER->IsAuthorized()) {
    LocalRedirect(SITE_DIR . 'personal/login/?back_url=' . urlencode($_SERVER['REQUEST_URI']));
}

$APPLICATION->SetTitle('Профиль и настройки');
$APPLICATION->SetPageProperty('body_class', 'page-lk page-lk-profile');
$APPLICATION->SetPageProperty('description', 'Управление профилем CHOKERZ');
?>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php'); ?>

<div class="lk-layout">
    <div class="lk-layout__container container">

        <?php $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/includes/lk/sidebar.php', [], ['MODE' => 'php']); ?>

        <div class="lk-layout__content lk-content">
            <?php
            $APPLICATION->IncludeComponent(
                'bitrix:main.profile',
                '.default',
                [
                    'PATH_TO_INDEX'  => '/personal/',
                    'CACHE_TYPE'     => 'N',
                    'SET_TITLE'      => 'N',
                    'SUCCESS_URL'    => '/personal/profile/?saved=y',
                ],
                false
            );
            ?>
        </div>

    </div>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
