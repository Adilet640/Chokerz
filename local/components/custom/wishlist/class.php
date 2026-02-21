<?php
/**
 * Компонент избранного (кастомный)
 * 
 * @author VibePilot
 * @version 1.0
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\TableManager;

/**
 * Class WishlistComponent
 */
class WishlistComponent extends CBitrixComponent
{
    /**
     * Идентификатор HL-блока для избранного
     */
    const HL_BLOCK_ID = 1; // Нужно настроить в админке (создать HL-блок "Избранное")

    /**
     * Выполнение компонента
     */
    public function executeComponent()
    {
        // Загрузка модулей
        if (!Loader::includeModule('highloadblock') || !Loader::includeModule('iblock')) {
            ShowError('Не удалось подключить модули');
            return;
        }

        // Проверка авторизации пользователя
        global $USER;
        if (!$USER->IsAuthorized()) {
            // Для неавторизованных пользователей можно использовать куки или сессию (не рекомендуется для продакшена)
            // Здесь просто показываем пустое избранное или сообщение о необходимости авторизации
            $this->arResult['ITEMS'] = [];
            $this->arResult['NEED_AUTH'] = true;
            $this->IncludeComponentTemplate();
            return;
        }

        // Получение ID текущего пользователя
        $userId = $USER->GetID();
        $this->arResult['USER_ID'] = $userId;
        $this->arResult['NEED_AUTH'] = false;

        // Получение товаров из избранного пользователя (если необходимо)
        $this->arResult['ITEMS'] = $this->getWishlistItems($userId);

        // Кэширование результата
        $this->IncludeComponentTemplate();
    }

    /**
     * Получение товаров из избранного пользователя (заглушка для будущей реализации)
     * 
     * @param int $userId ID пользователя
     * @return array Массив товаров из избранного (заглушка)
     */
    private function getWishlistItems($userId)
    {
        // Здесь будет логика получения товаров из избранного из БД (через запрос к таблице или компонент)
        return [];
    }
}
