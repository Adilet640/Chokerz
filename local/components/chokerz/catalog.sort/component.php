<?php
/**
 * Компонент: chokerz:catalog.sort
 * Назначение: попап-панель сортировки каталога по макету FILTER & POP UP
 *
 * Получает текущий параметр сортировки из GET, формирует список вариантов.
 * Варианты по ТЗ п. 7.1: дата, популярность, цена (asc/desc), рейтинг, материал, размер, наличие.
 * На макете показаны 4: популярность, дешевле, дороже, рейтинг — остальные опционально скрываются через параметр.
 *
 * Параметры компонента:
 *   SORT_BY     — текущее поле сортировки (по умолчанию 'SORT' = популярность)
 *   SORT_ORDER  — ASC | DESC
 *   VISIBLE_SORTS — массив символьных кодов для вывода (по умолчанию все)
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Application;
use Bitrix\Main\Context;

/** @var CBitrixComponent $this */

$request = Application::getInstance()->getContext()->getRequest();

// Допустимые варианты сортировки (символьный код => [поле Битрикс, направление, метка])
$sortOptions = [
    'popular'        => ['field' => 'SORT',           'order' => 'ASC',  'label' => 'По популярности'],
    'price-asc'      => ['field' => 'CATALOG_PRICE_1','order' => 'ASC',  'label' => 'Сначала дешевле'],
    'price-desc'     => ['field' => 'CATALOG_PRICE_1','order' => 'DESC', 'label' => 'Сначала дороже'],
    'rating'         => ['field' => 'PROPERTY_RATING','order' => 'DESC', 'label' => 'По рейтингу'],
    'date'           => ['field' => 'DATE_ACTIVE_FROM','order' => 'DESC', 'label' => 'По дате'],
    'material'       => ['field' => 'PROPERTY_MATERIAL','order' => 'ASC','label' => 'По материалу'],
    'size'           => ['field' => 'PROPERTY_SIZE',  'order' => 'ASC',  'label' => 'По размеру'],
    'available'      => ['field' => 'CATALOG_AVAILABLE','order' => 'DESC','label' => 'По наличию'],
];

// Видимые варианты из параметров компонента (по умолчанию первые 4 как на макете)
$visibleSorts = $arParams['VISIBLE_SORTS'] ?? ['popular', 'price-asc', 'price-desc', 'rating'];

// Определяем текущую сортировку из GET
$currentSortBy    = (string)($request->getQuery('sort_by')    ?? 'SORT');
$currentSortOrder = strtoupper((string)($request->getQuery('sort_order') ?? 'ASC'));

// Определяем активный вариант
$activeSort = 'popular';
foreach ($sortOptions as $code => $option) {
    if ($option['field'] === $currentSortBy && $option['order'] === $currentSortOrder) {
        $activeSort = $code;
        break;
    }
}

// Фильтруем по видимым
$filteredOptions = array_filter(
    $sortOptions,
    static fn(string $key): bool => in_array($key, $visibleSorts, true),
    ARRAY_FILTER_USE_KEY
);

$this->arResult = [
    'SORT_OPTIONS'    => $filteredOptions,
    'ACTIVE_SORT'     => $activeSort,
    'CURRENT_SORT_BY' => $currentSortBy,
    'CURRENT_ORDER'   => $currentSortOrder,
];

$this->IncludeComponentTemplate();
