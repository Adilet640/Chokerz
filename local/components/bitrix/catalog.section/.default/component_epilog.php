<?php
/**
 * component_epilog.php — bitrix:catalog.section / chokerz

 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Page\Asset;

// CSS / JS подключены в template.php через Asset::addCss/addJs
// Здесь только SEO-мета canonical

$navParam    = $arResult['NAV_PARAM_NAME'] ?? 'PAGEN_1';
$currentPage = (int)($arResult['NAV_RESULT']->NavPageNomer ?? 1);
$canonicalUrl = $APPLICATION->GetCurPage(false);

// Удаляем все параметры пагинации из канонического URL
$canonicalParams = $_GET;
unset($canonicalParams[$navParam]);
$canonicalFull = 'https://' . SITE_SERVER_NAME . $canonicalUrl
    . (!empty($canonicalParams) ? '?' . http_build_query($canonicalParams) : '');

if ($currentPage > 1) {
    // Страницы 2+ — canonical на первую страницу (без пагинации)
    $APPLICATION->SetPageProperty('canonical', $canonicalFull);
}
