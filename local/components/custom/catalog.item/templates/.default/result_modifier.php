if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$element = &$arResult['ELEMENT'];
$props   = &$element['PROPERTIES'];

// -----------------------------------------------------------------------
// 1. Форматирование цены (если class.php не сформировал)
// -----------------------------------------------------------------------
if (!empty($element['PRICE']) && empty($element['FORMATTED_PRICE'])) {
    $element['FORMATTED_PRICE'] = number_format(
        (float)$element['PRICE'], 0, '.', ' '
    ) . ' ' . ($element['CURRENCY'] ?? '₽');
}

// -----------------------------------------------------------------------
// 2. Нормализация hex-кода цвета из XML_ID enum-значения
// -----------------------------------------------------------------------
if (!empty($props['COLOR']['VALUE_XML_ID'])) {
    $hex = trim($props['COLOR']['VALUE_XML_ID']);
    if ($hex !== '' && $hex[0] !== '#') {
        $hex = '#' . $hex;
    }
    // Принимаем #RGB, #RRGGBB, #RRGGBBAA
    if (!preg_match('/^#[0-9A-Fa-f]{3}(?:[0-9A-Fa-f]{3}(?:[0-9A-Fa-f]{2})?)?$/', $hex)) {
        $hex = '';
    }
    $props['COLOR']['HEX'] = $hex;
} else {
    $props['COLOR']['HEX'] = '';
}

// -----------------------------------------------------------------------
// 3. Флаги бейджей
// -----------------------------------------------------------------------
$element['BADGES'] = [
    'HIT'  => !empty($props['HIT']['VALUE'])  && $props['HIT']['VALUE']  === 'Y',
    'NEW'  => !empty($props['NEW']['VALUE'])  && $props['NEW']['VALUE']  === 'Y',
    'SALE' => !empty($props['SALE']['VALUE']) && $props['SALE']['VALUE'] === 'Y',
];
$element['HAS_BADGES'] = in_array(true, $element['BADGES'], true);

// -----------------------------------------------------------------------
// 4. Флаг наличия
// -----------------------------------------------------------------------
$element['IS_AVAILABLE'] = (
    ($element['CATALOG_AVAILABLE'] ?? 'N') === 'Y'
    && (float)($element['CATALOG_QUANTITY'] ?? 0) > 0
);

// -----------------------------------------------------------------------
// 5. Флаг наличия ссылок на маркетплейсы
// -----------------------------------------------------------------------
$element['HAS_MARKETPLACE_LINKS'] = (
    !empty($props['OZON_LINK']['VALUE'])
    || !empty($props['WB_LINK']['VALUE'])
    || !empty($props['YM_LINK']['VALUE'])
);

// -----------------------------------------------------------------------
// 6. Подготовка офферов (SKU) для шаблона и для JS
// -----------------------------------------------------------------------
$offersForJs = [];

foreach ($arResult['OFFERS'] as &$offer) {
    // Нормализуем hex цвета оффера
    if (!empty($offer['COLOR_HEX'])) {
        $hex = trim($offer['COLOR_HEX']);
        if ($hex !== '' && $hex[0] !== '#') {
            $hex = '#' . $hex;
        }
        if (!preg_match('/^#[0-9A-Fa-f]{3}(?:[0-9A-Fa-f]{3}(?:[0-9A-Fa-f]{2})?)?$/', $hex)) {
            $hex = '';
        }
        $offer['COLOR_HEX'] = $hex;
    }

    $offer['IS_AVAILABLE'] = (float)($offer['QUANTITY'] ?? 0) > 0;

    $offersForJs[] = [
        'id'        => (int)$offer['ID'],
        'size'      => $offer['SIZE']      ?? null,
        'color'     => $offer['COLOR']     ?? null,
        'colorHex'  => $offer['COLOR_HEX'] ?? null,
        'price'     => (float)($offer['PRICE'] ?? 0),
        'available' => $offer['IS_AVAILABLE'],
    ];
}
unset($offer);

// JSON для data-атрибута в template.php
$arResult['OFFERS_JSON'] = htmlspecialchars(
    json_encode($offersForJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT),
    ENT_QUOTES,
    'UTF-8'
);

// -----------------------------------------------------------------------
// 7. JSON-LD (Schema.org Product) — выводится в component_epilog.php
// -----------------------------------------------------------------------
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'Product',
    'name'     => $element['NAME'] ?? '',
    'sku'      => $props['ARTICLE']['VALUE'] ?? '',
    'url'      => !empty($element['DETAIL_PAGE_URL'])
        ? 'https://' . $_SERVER['HTTP_HOST'] . $element['DETAIL_PAGE_URL']
        : '',
];

if (!empty($element['PREVIEW_PICTURE_SRC'])) {
    $jsonLd['image'] = 'https://' . $_SERVER['HTTP_HOST'] . $element['PREVIEW_PICTURE_SRC'];
}

if (!empty($element['PRICE'])) {
    $jsonLd['offers'] = [
        '@type'         => 'Offer',
        'price'         => (float)$element['PRICE'],
        'priceCurrency' => $element['CURRENCY'] ?? 'RUB',
        'availability'  => $element['IS_AVAILABLE']
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock',
        'url'           => $jsonLd['url'],
    ];
}

if (!empty($element['PREVIEW_TEXT'])) {
    $jsonLd['description'] = strip_tags($element['PREVIEW_TEXT']);
}

$arResult['JSON_LD'] = json_encode(
    $jsonLd,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
);


