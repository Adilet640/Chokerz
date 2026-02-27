<?php
/**
 * /local/components/chokerz/modal.manager/.parameters.php
 * Описание параметров компонента для административного интерфейса Битрикс.
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = [
    'GROUPS' => [
        'SOCIAL' => [
            'NAME' => 'Социальные сети (OAuth)',
            'SORT' => 100,
        ],
    ],
    'PARAMETERS' => [
        'SOCIAL_SERVICES' => [
            'PARENT'   => 'SOCIAL',
            'NAME'     => 'Активные соцсети для входа',
            'TYPE'     => 'LIST',
            'MULTIPLE' => 'Y',
            'VALUES'   => [
                'vk' => 'ВКонтакте',
                'ok' => 'Одноклассники',
                'tg' => 'Telegram',
            ],
            'DEFAULT' => ['vk', 'ok', 'tg'],
            'SORT'    => 110,
        ],
    ],
];
