<?php
/**
 * result_modifier.php — bitrix:catalog.section / .default
 * Проект: CHOKERZ
 *
 * Дополняет $arResult["ITEMS"] свойствами, ценами, изображениями и доступностью.
 * Кеш: управляемый кеш Битрикс с тегом iblock_id_N (D7).
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Data\TaggedCache;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Catalog\ProductTable;

// ─── Загрузка модулей ─────────────────────────────────────────────────────────
if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog')) {
    return;
}

// ─── Вспомогательные функции ──────────────────────────────────────────────────

/**
 * Нормализует hex-цвет из XML_ID enum-значения.
 */
function chkzSectionNormalizeHex(string $raw): string
{
    $hex = '#' . ltrim(trim($raw), '#');
    return preg_match('/^#[0-9A-Fa-f]{3}(?:[0-9A-Fa-f]{3}(?:[0-9A-Fa-f]{2})?)?$/', $hex)
        ? $hex : '';
}

/**
 * Форматирует цену в рублях.
 */
function chkzSectionFormatPrice(float $price): string
{
    return number_format($price, 0, '.', '&nbsp;') . '&nbsp;₽';
}

/**
 * Проверяет enum-свойство на Y/ДА.
 */
function chkzSectionIsFlagSet(?string $val): bool
{
    $v = mb_strtoupper((string)$val);
    return $v === 'Y' || $v === 'ДА';
}

// ─── Собираем IDs элементов ───────────────────────────────────────────────────
if (empty($arResult['ITEMS']) || !is_array($arResult['ITEMS'])) {
    return;
}

$iblockId  = (int)($arResult['IBLOCK']['ID'] ?? $arResult['IBLOCK_ID'] ?? 0);
$elementIds = array_column($arResult['ITEMS'], 'ID');
$elementIds = array_map('intval', array_filter($elementIds));

if (empty($elementIds) || $iblockId === 0) {
    return;
}

// ─── Кеш ─────────────────────────────────────────────────────────────────────
$cacheId  = 'chkz_section_items_' . md5(serialize($elementIds));
$cachePath = '/chokerz/catalog_section/';
$cacheTtl  = 3600;

$cache       = Cache::createInstance();
$taggedCache = new TaggedCache();

$propsData   = [];   // [elementId => [CODE => [...value data]]]
$productData = [];   // [elementId => ['AVAILABLE' => 'Y/N']]
$morePhotos  = [];   // [elementId => [[SRC, WIDTH, HEIGHT]]]

if ($cache->initCache($cacheTtl, $cacheId, $cachePath)) {
    $cached      = $cache->getVars();
    $propsData   = $cached['propsData']   ?? [];
    $productData = $cached['productData'] ?? [];
    $morePhotos  = $cached['morePhotos']  ?? [];
} else {
    $cache->startDataCache();
    $taggedCache->startTagCache($cachePath);
    $taggedCache->registerTag('iblock_id_' . $iblockId);

    // ── 1. Свойства через D7 PropertyTable + CIBlockElement::GetProperty ──────
    //    Используем GetPropertyValuesArray — официальный D7-совместимый способ
    //    получить все свойства сразу одним запросом.

    $needCodes = ['COLOR', 'MATERIAL', 'SIZE', 'HIT', 'NEW', 'SALE', 'RATING', 'ARTICLE', 'MORE_PHOTO'];

    // Получаем мета-информацию о свойствах (тип, enum и т.д.) через D7
    $propMeta = [];
    $propRes  = PropertyTable::getList([
        'filter' => [
            'IBLOCK_ID' => $iblockId,
            'CODE'      => $needCodes,
            'ACTIVE'    => 'Y',
        ],
        'select' => ['ID', 'CODE', 'PROPERTY_TYPE', 'MULTIPLE'],
    ]);
    while ($row = $propRes->fetch()) {
        $propMeta[$row['CODE']] = $row;
    }

    // GetPropertyValuesArray — выборка значений для всех элементов одним вызовом
    $rawProps = [];
    \CIBlockElement::GetPropertyValuesArray(
        $rawProps,
        $iblockId,
        ['ID' => $elementIds],
        ['CODE' => $needCodes]
    );

    // MORE_PHOTO — тип F (файл), обрабатываем отдельно
    foreach ($rawProps as $elId => $elProps) {
        $elId = (int)$elId;
        $propsData[$elId] = [];

        foreach ($needCodes as $code) {
            if ($code === 'MORE_PHOTO') {
                continue; // обрабатываем ниже
            }
            $propsData[$elId][$code] = $elProps[$code] ?? null;
        }

        // MORE_PHOTO
        if (!empty($elProps['MORE_PHOTO'])) {
            $morePhotoRaw = $elProps['MORE_PHOTO'];
            // Значения могут быть массивом или одним значением
            $fileIds = [];
            if (isset($morePhotoRaw['VALUE'])) {
                $vals = is_array($morePhotoRaw['VALUE']) ? $morePhotoRaw['VALUE'] : [$morePhotoRaw['VALUE']];
                foreach ($vals as $fId) {
                    $fId = (int)$fId;
                    if ($fId > 0) {
                        $fileIds[] = $fId;
                    }
                }
            }
            // Используем ~VALUE для RAW file IDs если VALUE пустой
            if (empty($fileIds) && isset($morePhotoRaw['~VALUE'])) {
                $rawVals = is_array($morePhotoRaw['~VALUE']) ? $morePhotoRaw['~VALUE'] : [$morePhotoRaw['~VALUE']];
                foreach ($rawVals as $fId) {
                    $fId = (int)$fId;
                    if ($fId > 0) {
                        $fileIds[] = $fId;
                    }
                }
            }

            $morePhotos[$elId] = [];
            foreach ($fileIds as $fileId) {
                $fileArr = \CFile::GetFileArray($fileId);
                if ($fileArr && !empty($fileArr['SRC'])) {
                    $morePhotos[$elId][] = [
                        'SRC'    => $fileArr['SRC'],
                        'WIDTH'  => (int)($fileArr['WIDTH']  ?? 800),
                        'HEIGHT' => (int)($fileArr['HEIGHT'] ?? 800),
                    ];
                }
            }
        }
    }

    // ── 2. Данные каталога (доступность) через D7 ProductTable ───────────────
    $productRes = ProductTable::getList([
        'filter' => ['=ID' => $elementIds],
        'select' => ['ID', 'AVAILABLE', 'QUANTITY', 'QUANTITY_TRACE', 'CAN_BUY_ZERO'],
    ]);
    while ($row = $productRes->fetch()) {
        $productData[(int)$row['ID']] = $row;
    }

    $taggedCache->endTagCache();
    $cache->endDataCache([
        'propsData'   => $propsData,
        'productData' => $productData,
        'morePhotos'  => $morePhotos,
    ]);
}

// ─── Применяем данные к $arResult['ITEMS'] ───────────────────────────────────
foreach ($arResult['ITEMS'] as &$item) {
    $elId = (int)$item['ID'];
    $ep   = $propsData[$elId] ?? [];
    $prod = $productData[$elId] ?? [];

    // ── Флаги (HIT, NEW, SALE) ────────────────────────────────────────────────
    $item['BADGES'] = [
        'HIT'  => chkzSectionIsFlagSet($ep['HIT']['VALUE']  ?? null),
        'NEW'  => chkzSectionIsFlagSet($ep['NEW']['VALUE']  ?? null),
        'SALE' => chkzSectionIsFlagSet($ep['SALE']['VALUE'] ?? null),
    ];
    $item['HAS_BADGES'] = in_array(true, $item['BADGES'], true);

    // ── Цвет ──────────────────────────────────────────────────────────────────
    $colorVal    = $ep['COLOR']['VALUE']          ?? '';
    $colorXmlId  = $ep['COLOR']['VALUE_XML_ID']   ?? '';
    $item['COLOR_LABEL'] = $colorVal;
    $item['COLOR_HEX']   = chkzSectionNormalizeHex((string)$colorXmlId);

    // ── Прочие свойства ───────────────────────────────────────────────────────
    $item['PROP_MATERIAL'] = $ep['MATERIAL']['VALUE'] ?? '';
    $item['PROP_SIZE']     = $ep['SIZE']['VALUE']     ?? '';
    $item['PROP_ARTICLE']  = $ep['ARTICLE']['VALUE']  ?? '';
    $item['PROP_RATING']   = isset($ep['RATING']['VALUE'])
        ? (float)$ep['RATING']['VALUE']
        : null;

    // ── Доступность ───────────────────────────────────────────────────────────
    $catalogAvailable = $prod['AVAILABLE'] ?? 'N';
    $item['IS_AVAILABLE'] = ($catalogAvailable === 'Y');

    // ── Галерея (GALLERY) ─────────────────────────────────────────────────────
    $gallery = [];

    // Главное изображение
    if (!empty($item['DETAIL_PICTURE']['SRC'])) {
        $gallery[] = [
            'SRC'    => $item['DETAIL_PICTURE']['SRC'],
            'ALT'    => htmlspecialchars($item['NAME'] ?? '', ENT_QUOTES, 'UTF-8'),
            'WIDTH'  => (int)($item['DETAIL_PICTURE']['WIDTH']  ?? 800),
            'HEIGHT' => (int)($item['DETAIL_PICTURE']['HEIGHT'] ?? 800),
        ];
    } elseif (!empty($item['PREVIEW_PICTURE']['SRC'])) {
        $gallery[] = [
            'SRC'    => $item['PREVIEW_PICTURE']['SRC'],
            'ALT'    => htmlspecialchars($item['NAME'] ?? '', ENT_QUOTES, 'UTF-8'),
            'WIDTH'  => (int)($item['PREVIEW_PICTURE']['WIDTH']  ?? 600),
            'HEIGHT' => (int)($item['PREVIEW_PICTURE']['HEIGHT'] ?? 600),
        ];
    }

    // MORE_PHOTO
    if (!empty($morePhotos[$elId])) {
        foreach ($morePhotos[$elId] as $photo) {
            $gallery[] = array_merge($photo, [
                'ALT' => htmlspecialchars($item['NAME'] ?? '', ENT_QUOTES, 'UTF-8'),
            ]);
        }
    }

    $item['GALLERY'] = $gallery;

    // ── Цены ──────────────────────────────────────────────────────────────────
    $item['PRICE_VALUE']        = null;
    $item['PRICE_OLD_VALUE']    = null;
    $item['PRICE_FORMATTED']    = '';
    $item['PRICE_OLD_FORMATTED'] = '';

    if (!empty($item['PRICES']['BASE'])) {
        $base = $item['PRICES']['BASE'];

        $discountVal = (float)($base['DISCOUNT_VALUE'] ?? 0);
        $baseVal     = (float)($base['VALUE']          ?? 0);

        $item['PRICE_VALUE']     = $discountVal > 0 ? $discountVal : $baseVal;
        $item['PRICE_OLD_VALUE'] = $baseVal;
        $item['CURRENCY']        = $base['CURRENCY'] ?? 'RUB';

        $item['PRICE_FORMATTED'] = chkzSectionFormatPrice($item['PRICE_VALUE']);

        if ($item['PRICE_OLD_VALUE'] > $item['PRICE_VALUE'] && $item['PRICE_OLD_VALUE'] > 0) {
            $item['PRICE_OLD_FORMATTED'] = chkzSectionFormatPrice($item['PRICE_OLD_VALUE']);
        }
    }

    // ── Процент скидки ────────────────────────────────────────────────────────
    $item['DISCOUNT_PERCENT'] = 0;
    if ($item['PRICE_OLD_VALUE'] > 0 && $item['PRICE_VALUE'] !== null
        && $item['PRICE_OLD_VALUE'] > $item['PRICE_VALUE']
    ) {
        $item['DISCOUNT_PERCENT'] = (int)round(
            (1 - $item['PRICE_VALUE'] / $item['PRICE_OLD_VALUE']) * 100
        );
    }
}
unset($item);
