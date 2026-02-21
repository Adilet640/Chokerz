<?php
/**
 * Шаблон компонента фильтра каталога товаров (кастомный)
 * 
 * @author VibePilot
 * @version 1.0
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Получение свойств инфоблока для фильтрации (заглушка для будущей реализации)
 */
$arResult['PROPERTIES'] = [];

// Тип изделия (список)
$arResult['PROPERTIES']['TYPE'] = [
    'NAME' => 'Тип изделия',
    'CODE' => 'TYPE',
    'VALUES' => [
        'ошейник' => 'Ошейник',
        'поводок' => 'Поводок',
        'намордник' => 'Намордник',
        'комплект' => 'Комплект'
    ]
];

// Материал (список)
$arResult['PROPERTIES']['MATERIAL'] = [
    'NAME' => 'Материал',
    'CODE' => 'MATERIAL',
    'VALUES' => [
        'кожа' => 'Кожа',
        'нейлон' => 'Нейлон',
        'металл' => 'Металл',
        'текстиль' => 'Текстиль'
    ]
];

// Цвет (список с цветными кружками)
$arResult['PROPERTIES']['COLOR'] = [
    'NAME' => 'Цвет',
    'CODE' => 'COLOR',
    'VALUES' => [
        'red' => [
            'NAME' => 'Красный',
            'HEX' => '#FF0000'
        ],
        'blue' => [
            'NAME' => 'Синий',
            'HEX' => '#0000FF'
        ],
        'black' => [
            'NAME' => 'Чёрный',
            'HEX' => '#000000'
        ],
        'green' => [
            'NAME' => 'Зелёный',
            'HEX' => '#00FF00'
        ]
    ]
];

// Размер (строка)
$arResult['PROPERTIES']['SIZE'] = [
    'NAME' => 'Размер',
    'CODE' => 'SIZE',
    'VALUES' => [
        'xs' => 'XS',
        's' => 'S',
        'm' => 'M',
        'l' => 'L',
        'xl' => 'XL'
    ]
];

// Флаги товара (Хит, Новинка, Акция)
$arResult['PROPERTIES']['FLAGS'] = [
    'NAME' => 'Особые предложения',
    'CODE' => 'FLAGS',
    'VALUES' => [
        'HIT' => 'Хит продаж',
        'NEW' => 'Новинка',
        'SALE' => 'Акция'
    ]
];

// Наличие товара (да/нет)
$arResult['PROPERTIES']['AVAILABILITY'] = [
    'NAME' => 'Наличие',
    'CODE' => 'AVAILABILITY',
    'VALUES' => [
        'Y' => 'В наличии',
        'N' => 'Нет в наличии'
    ]
];

// Сортировка (по умолчанию)
$arResult['SORTING'] = [
    'default' => 'По умолчанию',
    'date_desc' => 'По дате (новые)',
    'date_asc' => 'По дате (старые)',
    'price_asc' => 'По цене (возрастание)',
    'price_desc' => 'По цене (убывание)',
    'name' => 'По названию'
];

// Текущая сортировка из запроса или по умолчанию
global $APPLICATION;
$arResult['CURRENT_SORT'] = $_REQUEST['sort'] ?? 'default';
$arResult['CURRENT_ORDER'] = $_REQUEST['order'] ?? 'desc';

// Формирование страницы (заглушка для будущей реализации)
$this->IncludeComponentTemplate();
