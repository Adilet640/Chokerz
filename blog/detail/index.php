<?php
/**
 * Детальная страница блога CHOKERZ
 * URL: /blog/#CODE#/
 * Символьный код берётся из переменной $CODE (настраивается в urlrewrite.php)
 *
 * Title и description устанавливаются внутри компонента custom:blog.detail
 * через $APPLICATION->SetTitle() и $APPLICATION->SetPageProperty('description', ...)
 * на основании полей элемента инфоблока (NAME и PREVIEW_TEXT / свойство SEO_DESCRIPTION).
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Config\Option;

$APPLICATION->SetPageProperty('body_class', 'page-blog-detail');

// Хлебные крошки
$APPLICATION->IncludeComponent('bitrix:breadcrumb', '.default', [
    'PATH'       => '',
    'SITE_ID'    => SITE_ID,
    'CACHE_TYPE' => 'A',
    'CACHE_TIME' => 86400,
]);

$APPLICATION->IncludeComponent(
    'custom:blog.detail',
    '.default',
    [
        'IBLOCK_TYPE'   => 'blog',
        'IBLOCK_ID'     => Option::get('chokerz', 'blog_iblock_id', 0),
        'ELEMENT_CODE'  => $CODE ?? '',   // $CODE передаётся из urlrewrite.php
        'RELATED_COUNT' => 4,
        'LIST_URL'      => '/blog/',
        'CACHE_TYPE'    => 'A',
        'CACHE_TIME'    => 3600,
    ],
    false
);
