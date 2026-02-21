<?php
/**
 * Компонент фильтра каталога товаров (кастомный)
 * 
 * @author VibePilot
 * @version 1.0
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\SectionTable;

/**
 * Class CatalogFilterComponent
 */
class CatalogFilterComponent extends CBitrixComponent
{
    /**
     * Выполнение компонента
     */
    public function executeComponent()
    {
        // Загрузка модуля инфоблоков и каталога
        if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog')) {
            ShowError('Не удалось подключить модули инфоблоков или каталога');
            return;
        }

        // Получение параметров компонента или значений по умолчанию
        $this->arParams['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'] ?: 1; // ID инфоблока каталога товаров (нужно настроить)
        $this->arParams['CACHE_TIME'] = $this->arParams['CACHE_TIME'] ?: 3600;
        
        // Подключение шаблона компонента, если он существует, или вывод ошибки при его отсутствии.
        $this->includeComponentTemplate();
    }
}
