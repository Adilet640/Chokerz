<?php
/**
 * /local/components/bitrix/catalog.smart.filter/templates/chokerz/result_modifier.php
 *
 * Подключает JS catalog-filter.js через стандартный Битрикс Asset.
 * result_modifier.php вызывается после component.php, до template.php.
 * Используем его для регистрации JS-ресурса без inline-скрипта в шаблоне.
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Page\Asset;

// JS подключается с defer (Asset добавляет атрибут defer автоматически для файлов)
Asset::getInstance()->addJs('/local/templates/chokerz/js/catalog-filter.js');
