<?php
/**
 * AJAX endpoint — подсказки живого поиска (хедер) CHOKERZ
 * URL: /local/ajax/search-suggest.php?q=QUERY
 *
 * Возвращает JSON:
 * {
 *   "products": [ {id, name, url, price, img} … ],  // до 5
 *   "info":     [ {title, url, type} … ],            // до 3
 *   "total":    N
 * }
 */

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
require_once $DOCUMENT_ROOT . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Config\Option;

header('Content-Type: application/json; charset=utf-8');

// Только GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    die();
}

$query = trim($_GET['q'] ?? '');

if (mb_strlen($query) < 2) {
    echo json_encode(['products' => [], 'info' => [], 'total' => 0], JSON_UNESCAPED_UNICODE);
    die();
}

if (!Loader::includeModule('iblock') || !Loader::includeModule('search')) {
    http_response_code(500);
    echo json_encode(['error' => 'Modules not loaded'], JSON_UNESCAPED_UNICODE);
    die();
}

$catalogIblockId = (int)Option::get('chokerz', 'catalog_iblock_id', 0);
$blogIblockId    = (int)Option::get('chokerz', 'blog_iblock_id',    0);
$pagesIblockId   = (int)Option::get('chokerz', 'pages_iblock_id',   0);

// Кеш на 2 минуты
$cacheId  = 'search_suggest_' . md5($query . $catalogIblockId);
$cacheDir = '/search/suggest';
$cache    = Cache::createInstance();

if ($cache->initCache(120, $cacheId, $cacheDir)) {
    echo json_encode($cache->getVars(), JSON_UNESCAPED_UNICODE);
    die();
}

$result = ['products' => [], 'info' => [], 'total' => 0];

// ——— Товары ———
if ($catalogIblockId > 0) {
    $res = \CIBlockElement::GetList(
        ['NAME' => 'ASC'],
        [
            'IBLOCK_ID'          => $catalogIblockId,
            'ACTIVE'             => 'Y',
            'SEARCHABLE_CONTENT' => '%' . $query . '%',
        ],
        false,
        ['nTopCount' => 5],
        ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'CATALOG_PRICE_1']
    );

    while ($el = $res->GetNextElement()) {
        $fields = $el->GetFields();
        $img    = '';
        if ($fields['PREVIEW_PICTURE']) {
            $file = \CFile::GetFileArray($fields['PREVIEW_PICTURE']);
            $img  = $file ? \CFile::GetFileSRC($file) : '';
        }
        $result['products'][] = [
            'id'    => (int)$fields['ID'],
            'name'  => $fields['NAME'],
            'url'   => $fields['DETAIL_PAGE_URL'],
            'price' => (float)($fields['CATALOG_PRICE_1'] ?? 0),
            'img'   => $img,
        ];
    }
}

// ——— Информационные материалы ———
$infoIblocks = array_filter([$blogIblockId, $pagesIblockId]);
if (!empty($infoIblocks)) {
    $obSearch = new CSearch();
    $obSearch->SetOptions(['STEMMING' => true]);
    $obSearch->Search(
        ['QUERY' => $query, 'SITE_ID' => SITE_ID, 'MODULE' => 'iblock'],
        ['RANK' => 'DESC'],
        []
    );
    $obSearch->NavStart(3, false, 1);

    while ($row = $obSearch->GetNext()) {
        $iblockId = (int)($row['PARAM1'] ?? 0);
        if (!in_array($iblockId, $infoIblocks, true)) {
            continue;
        }
        $result['info'][] = [
            'title' => $row['TITLE'] ?? '',
            'url'   => $row['URL']   ?? '',
            'type'  => $iblockId === $blogIblockId ? 'blog' : 'page',
        ];
    }
}

$result['total'] = count($result['products']) + count($result['info']);

if ($cache->startDataCache()) {
    $cache->endDataCache($result);
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
