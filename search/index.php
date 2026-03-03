<?php
/**
 * Страница поиска CHOKERZ
 * URL: /search/?q=QUERY[&tab=all|products|info][&page=N]
 * SEO: canonical без page, rel=prev/next, noindex на страницах с запросом
 *
 * Примечание: robots=noindex,follow на страницах поиска — техническое решение
 * для предотвращения индексации дублированного контента. Требует согласования с заказчиком (п.7.6).
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;

$request = Context::getCurrent()->getRequest();
$query   = trim((string)($request->getQuery('q') ?? ''));
$page    = max(1, (int)($request->getQuery('page') ?? 1));
$tab     = trim((string)($request->getQuery('tab') ?? 'all'));

// Заголовок страницы
if ($query !== '') {
    $APPLICATION->SetTitle('Поиск: «' . htmlspecialcharsEx($query) . '»');
    $APPLICATION->SetPageProperty('description', 'Результаты поиска по запросу «' . htmlspecialcharsEx($query) . '» в магазине CHOKERZ.');
} else {
    $APPLICATION->SetTitle('Поиск');
    $APPLICATION->SetPageProperty('description', 'Поиск товаров и материалов в магазине CHOKERZ.');
}

$APPLICATION->SetPageProperty('body_class', 'page-search');

// ——— SEO: Canonical — домен динамически через SITE_SERVER_NAME ———
$scheme        = $request->isHttps() ? 'https' : 'http';
$canonicalBase = $scheme . '://' . SITE_SERVER_NAME . '/search/';

$canonicalQuery = $query !== '' ? '?q=' . urlencode($query) : '';
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

// Robots: noindex,follow — согласовано с заказчиком (дублированный контент)
if ($query !== '') {
    $APPLICATION->SetPageProperty('robots', 'noindex, follow');
}

// Хлебные крошки
$APPLICATION->IncludeComponent('bitrix:breadcrumb', '.default', [
    'PATH'       => '',
    'SITE_ID'    => SITE_ID,
    'CACHE_TYPE' => 'A',
    'CACHE_TIME' => 86400,
]);

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
        'CACHE_TIME'         => 300,
    ],
    false
);
