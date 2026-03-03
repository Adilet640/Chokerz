<?php
/**
 * Страница редактирования профиля ЛК CHOKERZ
 * URL: /personal/profile/
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Context;

// Редирект неавторизованных — D7 API (CUser запрещён, п.10.2)
$currentUser = Context::getCurrent()->getUser();
if (!$currentUser || !$currentUser->isAuthorized()) {
    LocalRedirect(SITE_DIR . 'personal/login/?back_url=' . urlencode($_SERVER['REQUEST_URI']));
}

$APPLICATION->SetTitle('Мой профиль');
$APPLICATION->SetPageProperty('description', 'Редактирование личных данных и контактов в магазине CHOKERZ.');
$APPLICATION->SetPageProperty('body_class', 'page-lk page-lk-profile');
?>

<div class="lk-layout">
    <div class="lk-layout__container container">

        <?php $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/includes/lk/sidebar.php', [], ['MODE' => 'php']); ?>

        <div class="lk-layout__content lk-content">
            <?php
            $APPLICATION->IncludeComponent(
                'bitrix:system.auth.form',
                'profile',
                [
                    'CACHE_TYPE' => 'N',
                    'SET_TITLE'  => 'N',
                ],
                false
            );
            ?>
        </div>

    </div>
</div>
