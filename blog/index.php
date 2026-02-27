<?php
/**
 * Страница списка блога CHOKERZ
 * URL: /blog/
 * SEO: canonical, пагинация rel=prev/next, robots
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Config\Option;

$APPLICATION->SetTitle('Статьи и видео об амуниции и уходе');
$APPLICATION->SetPageProperty('description', 'Советы по выбору, уходу за животными и тренировкам. Статьи и видео от CHOKERZ.');
$APPLICATION->SetPageProperty('body_class', 'page-blog');

// Canonical и пагинация для SEO
$page         = max(1, (int)($_GET['page'] ?? 1));
$canonicalBase = 'https://chokerz.ru/blog/';

if ($page === 1) {
    $APPLICATION->SetPageProperty('canonical', $canonicalBase);
} else {
    // Со 2+ страницы — canonical на корень категории
    $APPLICATION->SetPageProperty('canonical', $canonicalBase);
    // 301 для page=1 реализован через .htaccess / nginx-redirect
}

// rel=prev/next
if ($page > 1) {
    $APPLICATION->SetPageProperty('rel_prev', $canonicalBase . ($page > 2 ? '?page=' . ($page - 1) : ''));
}
$APPLICATION->SetPageProperty('rel_next', $canonicalBase . '?page=' . ($page + 1));

// На страницах пагинации дублируем title/description родителя (ТЗ §8.2)
if ($page > 1) {
    $APPLICATION->SetTitle('Статьи и видео об амуниции и уходе');
}
?>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php'); ?>

<?php
// Хлебные крошки
$APPLICATION->IncludeComponent('bitrix:breadcrumb', '.default', [
    'PATH'                => '',
    'SITE_ID'             => SITE_ID,
    'START_FROM'          => 0,
    'CACHE_TYPE'          => 'A',
    'CACHE_TIME'          => 86400,
]);
?>

<?php
$APPLICATION->IncludeComponent(
    'custom:blog.list',
    '.default',
    [
        'IBLOCK_TYPE'  => 'blog',
        'IBLOCK_ID'    => Option::get('chokerz', 'blog_iblock_id', 0),
        'PAGE_SIZE'    => 9,
        'DETAIL_URL'   => '/blog/#CODE#/',
        'CACHE_TYPE'   => 'A',
        'CACHE_TIME'   => 3600,
    ],
    false
);
?>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
