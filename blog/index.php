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
use Bitrix\Main\Context;

$APPLICATION->SetTitle('Статьи и видео об амуниции и уходе');
$APPLICATION->SetPageProperty('description', 'Советы по выбору, уходу за животными и тренировкам. Статьи и видео от CHOKERZ.');
$APPLICATION->SetPageProperty('body_class', 'page-blog');

// ——— SEO: Canonical и пагинация ———
$request = Context::getCurrent()->getRequest();
$page    = max(1, (int)($request->getQuery('page') ?? 1));

// Домен определяется динамически — без хардкода
$scheme        = $request->isHttps() ? 'https' : 'http';
$canonicalBase = $scheme . '://' . SITE_SERVER_NAME . '/blog/';

// Все страницы пагинации получают canonical на первую (без параметра page)
$APPLICATION->SetPageProperty('canonical', $canonicalBase);

// rel=prev
if ($page > 1) {
    $APPLICATION->SetPageProperty('rel_prev', $canonicalBase . ($page > 2 ? '?page=' . ($page - 1) : ''));
}
// rel=next: выставляется как сигнал о наличии следующей страницы.
// Компонент custom:blog.list снимает это свойство на последней странице (п.ТЗ §8.3).
$APPLICATION->SetPageProperty('rel_next', $canonicalBase . '?page=' . ($page + 1));

// Хлебные крошки
$APPLICATION->IncludeComponent('bitrix:breadcrumb', '.default', [
    'PATH'       => '',
    'SITE_ID'    => SITE_ID,
    'START_FROM' => 0,
    'CACHE_TYPE' => 'A',
    'CACHE_TIME' => 86400,
]);

$APPLICATION->IncludeComponent(
    'custom:blog.list',
    '.default',
    [
        'IBLOCK_TYPE' => 'blog',
        'IBLOCK_ID'   => Option::get('chokerz', 'blog_iblock_id', 0),
        'PAGE_SIZE'   => 9,
        'DETAIL_URL'  => '/blog/#CODE#/',
        'CACHE_TYPE'  => 'A',
        'CACHE_TIME'  => 3600,
    ],
    false
);
