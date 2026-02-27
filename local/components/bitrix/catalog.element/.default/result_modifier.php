<?php


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$element = &$arResult;
$props   = &$arResult['PROPERTIES'];

// ─────────────────────────────────────────────────────────────────────────────
// 1. Галерея изображений
// ─────────────────────────────────────────────────────────────────────────────
$gallery = [];

// Основное детальное изображение
if (!empty($arResult['DETAIL_PICTURE']['SRC'])) {
    $gallery[] = [
        'SRC'   => $arResult['DETAIL_PICTURE']['SRC'],
        'ALT'   => htmlspecialchars($arResult['NAME'], ENT_QUOTES, 'UTF-8'),
        'WIDTH' => (int)($arResult['DETAIL_PICTURE']['WIDTH']  ?? 800),
        'HEIGHT'=> (int)($arResult['DETAIL_PICTURE']['HEIGHT'] ?? 800),
    ];
} elseif (!empty($arResult['PREVIEW_PICTURE']['SRC'])) {
    $gallery[] = [
        'SRC'   => $arResult['PREVIEW_PICTURE']['SRC'],
        'ALT'   => htmlspecialchars($arResult['NAME'], ENT_QUOTES, 'UTF-8'),
        'WIDTH' => (int)($arResult['PREVIEW_PICTURE']['WIDTH']  ?? 600),
        'HEIGHT'=> (int)($arResult['PREVIEW_PICTURE']['HEIGHT'] ?? 600),
    ];
}

// Дополнительные фотографии из свойства MORE_PHOTO (тип F, множественное)
if (!empty($props['MORE_PHOTO']['~VALUE']) && is_array($props['MORE_PHOTO']['~VALUE'])) {
    foreach ($props['MORE_PHOTO']['~VALUE'] as $fileId) {
        $fileId = (int)$fileId;
        if ($fileId > 0) {
            $fileData = \CFile::GetFileArray($fileId);
            if ($fileData && !empty($fileData['SRC'])) {
                $gallery[] = [
                    'SRC'    => $fileData['SRC'],
                    'ALT'    => htmlspecialchars($arResult['NAME'], ENT_QUOTES, 'UTF-8'),
                    'WIDTH'  => (int)($fileData['WIDTH']  ?? 800),
                    'HEIGHT' => (int)($fileData['HEIGHT'] ?? 800),
                ];
            }
        }
    }
}

$arResult['GALLERY'] = $gallery;

// ─────────────────────────────────────────────────────────────────────────────
// 2. Цена
// ─────────────────────────────────────────────────────────────────────────────
$arResult['PRICE_VALUE']     = null;
$arResult['PRICE_OLD_VALUE'] = null;
$arResult['PRICE_FORMATTED'] = '';
$arResult['CURRENCY']        = 'RUB';

if (!empty($arResult['PRICES']['BASE'])) {
    $basePrice = $arResult['PRICES']['BASE'];
    $arResult['PRICE_VALUE']     = (float)($basePrice['DISCOUNT_VALUE'] ?? $basePrice['VALUE'] ?? 0);
    $arResult['PRICE_OLD_VALUE'] = (float)($basePrice['VALUE'] ?? 0);
    $arResult['CURRENCY']        = $basePrice['CURRENCY'] ?? 'RUB';

    $arResult['PRICE_FORMATTED'] = number_format($arResult['PRICE_VALUE'], 0, '.', '&nbsp;')
        . '&nbsp;₽';

    // Старая цена выводится только если есть скидка
    if ($arResult['PRICE_OLD_VALUE'] > $arResult['PRICE_VALUE']) {
        $arResult['PRICE_OLD_FORMATTED'] = number_format(
            $arResult['PRICE_OLD_VALUE'], 0, '.', '&nbsp;'
        ) . '&nbsp;₽';
    } else {
        $arResult['PRICE_OLD_FORMATTED'] = '';
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. Бейджи
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Возвращает значение enum/list свойства в верхнем регистре для сравнения.
 */
function chkzPropVal(array $props, string $code): string
{
    return mb_strtoupper((string)($props[$code]['VALUE'] ?? ''));
}

$arResult['BADGES'] = [
    'HIT'  => chkzPropVal($props, 'HIT')  === 'Y' || chkzPropVal($props, 'HIT')  === 'ДА',
    'NEW'  => chkzPropVal($props, 'NEW')  === 'Y' || chkzPropVal($props, 'NEW')  === 'ДА',
    'SALE' => chkzPropVal($props, 'SALE') === 'Y' || chkzPropVal($props, 'SALE') === 'ДА',
];
$arResult['HAS_BADGES'] = in_array(true, $arResult['BADGES'], true);

// ─────────────────────────────────────────────────────────────────────────────
// 4. Наличие
// ─────────────────────────────────────────────────────────────────────────────
$arResult['IS_AVAILABLE'] = (
    ($arResult['CAN_BUY'] ?? false) === true
    || (float)($arResult['CATALOG_QUANTITY'] ?? 0) > 0
);

// ─────────────────────────────────────────────────────────────────────────────
// 5. Маркетплейс-ссылки
// ─────────────────────────────────────────────────────────────────────────────
$arResult['MARKETPLACE_LINKS'] = [
    'OZON' => !empty($props['OZON_LINK']['VALUE']) ? $props['OZON_LINK']['VALUE'] : '',
    'WB'   => !empty($props['WB_LINK']['VALUE'])   ? $props['WB_LINK']['VALUE']   : '',
    'YM'   => !empty($props['YM_LINK']['VALUE'])   ? $props['YM_LINK']['VALUE']   : '',
];
$arResult['HAS_MARKETPLACE_LINKS'] = array_filter($arResult['MARKETPLACE_LINKS']) !== [];

// ─────────────────────────────────────────────────────────────────────────────
// 6. Обработка офферов (SKU): цвета, размеры, JSON
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Нормализует hex-код цвета из XML_ID поля enum.
 */
function chkzNormalizeHex(string $raw): string
{
    $hex = trim($raw);
    if ($hex === '') {
        return '';
    }
    if ($hex[0] !== '#') {
        $hex = '#' . $hex;
    }
    return preg_match('/^#[0-9A-Fa-f]{3}(?:[0-9A-Fa-f]{3}(?:[0-9A-Fa-f]{2})?)?$/', $hex)
        ? $hex
        : '';
}

$colors  = []; // уникальные цвета: hex → label
$sizes   = []; // уникальные размеры: value → IS_AVAILABLE
$offersJs = [];

if (!empty($arResult['OFFERS']) && is_array($arResult['OFFERS'])) {
    foreach ($arResult['OFFERS'] as &$offer) {
        // Нормализуем hex цвета оффера
        $colorRaw = $offer['PROPERTIES']['COLOR']['VALUE_XML_ID'] ?? '';
        $colorHex = chkzNormalizeHex($colorRaw);
        $colorLabel = $offer['PROPERTIES']['COLOR']['VALUE'] ?? '';

        $offer['COLOR_HEX']   = $colorHex;
        $offer['COLOR_LABEL'] = $colorLabel;

        $sizeVal  = $offer['PROPERTIES']['SIZE']['VALUE'] ?? '';
        $offerQty = (float)($offer['CATALOG_QUANTITY'] ?? 0);
        $offerAvail = $offerQty > 0;
        $offer['IS_AVAILABLE'] = $offerAvail;

        // Оффер-цена
        $offerPriceVal = 0;
        if (!empty($offer['PRICES']['BASE'])) {
            $offerPriceVal = (float)($offer['PRICES']['BASE']['DISCOUNT_VALUE']
                ?? $offer['PRICES']['BASE']['VALUE'] ?? 0);
        }
        $offer['PRICE_VALUE'] = $offerPriceVal;

        // Собираем уникальные цвета
        if ($colorHex !== '' && $colorLabel !== '') {
            $colors[$colorHex] = $colorLabel;
        }

        // Собираем уникальные размеры
        if ($sizeVal !== '') {
            if (!isset($sizes[$sizeVal])) {
                $sizes[$sizeVal] = $offerAvail;
            } elseif ($offerAvail) {
                $sizes[$sizeVal] = true; // хотя бы один доступен
            }
        }

        $offersJs[] = [
            'id'        => (int)$offer['ID'],
            'size'      => $sizeVal,
            'color'     => $colorLabel,
            'colorHex'  => $colorHex,
            'price'     => $offerPriceVal,
            'available' => $offerAvail,
        ];
    }
    unset($offer);
}

$arResult['COLORS']     = $colors;
$arResult['SIZES']      = $sizes;
$arResult['OFFERS_JSON'] = htmlspecialchars(
    json_encode($offersJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT),
    ENT_QUOTES,
    'UTF-8'
);

// ─────────────────────────────────────────────────────────────────────────────
// 7. Характеристики (вкладка)
// ─────────────────────────────────────────────────────────────────────────────
$specs = [];
$specMap = [
    'ARTICLE'  => 'Артикул',
    'TYPE'     => 'Тип изделия',
    'MATERIAL' => 'Материал',
    'PURPOSE'  => 'Назначение',
    'SIZE'     => 'Размер',
    'COLOR'    => 'Цвет',
];
foreach ($specMap as $code => $label) {
    $val = $props[$code]['VALUE'] ?? '';
    if ($val !== '' && $val !== null) {
        $specs[] = [
            'LABEL' => $label,
            'VALUE' => htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8'),
        ];
    }
}
$arResult['SPECS'] = $specs;

// ─────────────────────────────────────────────────────────────────────────────
// 8. JSON-LD Schema.org Product (выводится в component_epilog.php)
// ─────────────────────────────────────────────────────────────────────────────
$protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl   = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? SITE_SERVER_NAME);
$detailUrl = $arResult['DETAIL_PAGE_URL'] ?? '';

$jsonLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'Product',
    'name'     => $arResult['NAME'] ?? '',
    'sku'      => $props['ARTICLE']['VALUE'] ?? '',
    'url'      => $baseUrl . $detailUrl,
];

if (!empty($gallery[0]['SRC'])) {
    $jsonLd['image'] = $baseUrl . $gallery[0]['SRC'];
}

if (!empty($arResult['PREVIEW_TEXT'])) {
    $jsonLd['description'] = strip_tags($arResult['PREVIEW_TEXT']);
}

if ($arResult['PRICE_VALUE'] !== null) {
    $jsonLd['offers'] = [
        '@type'         => 'Offer',
        'price'         => $arResult['PRICE_VALUE'],
        'priceCurrency' => $arResult['CURRENCY'],
        'availability'  => $arResult['IS_AVAILABLE']
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock',
        'url'           => $baseUrl . $detailUrl,
    ];
}

$arResult['JSON_LD'] = json_encode(
    $jsonLd,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
);
