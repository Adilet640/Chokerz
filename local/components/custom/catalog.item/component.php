<?php
/**
 * Шаблон компонента карточки товара (кастомный)
 * 
 * @author VibePilot
 * @version 1.0
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$element = $arResult['ELEMENT'];

// Хелпер для получения HEX цвета из названия
function getColorHex($colorName)
{
    $colors = [
        'Красный' => '#FF0000',
        'Синий' => '#0000FF',
        'Чёрный' => '#000000',
        'Зелёный' => '#00FF00',
        'Белый' => '#FFFFFF',
        'Серый' => '#808080',
        'Коричневый' => '#8B4513',
        'Бежевый' => '#F5F5DC'
    ];
    
    return $colors[$colorName] ?? '#CCCCCC';
}

$this->SetResultCacheKeys(['ELEMENT']);
$this->IncludeComponentTemplate();
