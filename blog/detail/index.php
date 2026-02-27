<?php
/**
 * Детальная страница блога CHOKERZ
 * URL: /blog/#CODE#/
 * Символьный код берётся из переменной $CODE (настраивается в urlrewrite.php)
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Config\Option;

$APPLICATION->SetPageProperty('body_class', 'page-blog-detail');
?>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php'); ?>

<?php
// Хлебные крошки
$APPLICATION->IncludeComponent('bitrix:breadcrumb', '.default', [
    'PATH'      => '',
    'SITE_ID'   => SITE_ID,
    'CACHE_TYPE'=> 'A',
    'CACHE_TIME'=> 86400,
]);
?>

<?php
$APPLICATION->IncludeComponent(
    'custom:blog.detail',
    '.default',
    [
        'IBLOCK_TYPE'    => 'blog',
        'IBLOCK_ID'      => Option::get('chokerz', 'blog_iblock_id', 0),
        'ELEMENT_CODE'   => $CODE ?? '',   // $CODE передаётся из urlrewrite.php
        'RELATED_COUNT'  => 4,
        'LIST_URL'       => '/blog/',
        'CACHE_TYPE'     => 'A',
        'CACHE_TIME'     => 3600,
    ],
    false
);
?>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
