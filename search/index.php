<?php
/**
 * Страница поиска CHOKERZ
 * URL: /search/?q=QUERY[&tab=all|products|info][&page=N][&sort=...]
 * SEO: canonical без page=1, rel=canonical на всех страницах пагинации
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Config\Option;

$query = trim($_GET['q'] ?? '');
$page  = max(1, (int)($_GET['page'] ?? 1));

// Заголовок страницы
if ($query !== '') {
    $APPLICATION->SetTitle('Поиск: «' . htmlspecialcharsEx($query) . '»');
    $APPLICATION->SetPageProperty('description', 'Результаты поиска по запросу «' . htmlspecialcharsEx($query) . '» в магазине CHOKERZ.');
} else {
    $APPLICATION->SetTitle('Поиск');
    $APPLICATION->SetPageProperty('description', 'Поиск товаров и материалов в магазине CHOKERZ.');
}

$APPLICATION->SetPageProperty('body_class', 'page-search');

// ——— SEO: Canonical ———
$canonicalBase = 'https://chokerz.ru/search/';
$canonicalQuery = $query !== '' ? '?q=' . urlencode($query) : '';

// tab если не all — добавляем
$tab = trim($_GET['tab'] ?? 'all');
if ($tab !== 'all' && $tab !== '') {
    $canonicalQuery .= ($canonicalQuery ? '&' : '?') . 'tab=' . urlencode($tab);
}

// Все страницы пагинации получают canonical на первую (без page)
$APPLICATION->SetPageProperty('canonical', $canonicalBase . $canonicalQuery);

// rel=prev / rel=next
if ($page > 1) {
    $prevQuery = $canonicalQuery . ($canonicalQuery ? '&' : '?') . 'page=' . ($page - 1);
    $APPLICATION->SetPageProperty('rel_prev', $canonicalBase . $prevQuery);
}
$APPLICATION->SetPageProperty('rel_next', $canonicalBase . $canonicalQuery . ($canonicalQuery ? '&' : '?') . 'page=' . ($page + 1));

// Robots: страницы поиска закрываем от индексации (дублированный контент)
if ($query !== '') {
    $APPLICATION->SetPageProperty('robots', 'noindex, follow');
}
?>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php'); ?>

<?php
$APPLICATION->IncludeComponent('bitrix:breadcrumb', '.default', [
    'PATH'      => '',
    'SITE_ID'   => SITE_ID,
    'CACHE_TYPE'=> 'A',
    'CACHE_TIME'=> 86400,
]);
?>

<?php
$APPLICATION->IncludeComponent(
    'custom:search.results',
    '.default',
    [
        'CATALOG_IBLOCK_ID'  => Option::get('chokerz', 'catalog_iblock_id', 0),
        'BLOG_IBLOCK_ID'     => Option::get('chokerz', 'blog_iblock_id',    0),
        'PAGES_IBLOCK_ID'    => Option::get('chokerz', 'pages_iblock_id',   0),
        'PRODUCTS_PAGE_SIZE' => 12,
        'INFO_PAGE_SIZE'     => 6,
        'CACHE_TYPE'         => 'A',
        'CACHE_TIME'         => 300,  // 5 минут — поиск должен быть свежим
    ],
    false
);
?>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
