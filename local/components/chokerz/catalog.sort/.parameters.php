<?php
/**
 * /local/components/chokerz/catalog.sort/.parameters.php
 * Описание параметров компонента для административного интерфейса Битрикс.
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = [
    'GROUPS' => [
        'SORT_SETTINGS' => [
            'NAME' => 'Настройки сортировки',
            'SORT' => 100,
        ],
    ],
    'PARAMETERS' => [
        'VISIBLE_SORTS' => [
            'PARENT'   => 'SORT_SETTINGS',
            'NAME'     => 'Видимые варианты сортировки',
            'TYPE'     => 'LIST',
            'MULTIPLE' => 'Y',
            'VALUES'   => [
                'popular'    => 'По популярности',
                'price-asc'  => 'Сначала дешевле',
                'price-desc' => 'Сначала дороже',
                'rating'     => 'По рейтингу',
                'date'       => 'По дате',
                'material'   => 'По материалу',
                'size'       => 'По размеру',
                'available'  => 'По наличию',
            ],
            'DEFAULT' => ['popular', 'price-asc', 'price-desc', 'rating'],
            'SORT'    => 110,
        ],
    ],
];
