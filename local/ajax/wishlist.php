<?php
/**
 * AJAX-обработчик избранного (Wishlist)
 * Маршрут: /local/ajax/wishlist.php
 *
 * @project CHOKERZ
 * @author  VibePilot
 * @version 2.1
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Application;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;

// ── Заголовки ────────────────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// ── Утилита ответа ────────────────────────────────────────────────────────────
function jsonResponse(array $data): void
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    die();
}

// ── Только POST ───────────────────────────────────────────────────────────────
$request = Application::getInstance()->getContext()->getRequest();
if (!$request->isPost()) {
    jsonResponse(['success' => false, 'message' => 'Метод не разрешён']);
}

// ── Входные данные ────────────────────────────────────────────────────────────
$action    = (string)$request->getPost('action');
$productId = (int)$request->getPost('productId');

// ── Авторизация (D7) ──────────────────────────────────────────────────────────
$currentUser = CurrentUser::get();
$isAuth      = $currentUser->isAuthorized();
$userId      = $isAuth ? (int)$currentUser->getId() : 0;

// ── CSRF — только для мутирующих действий ────────────────────────────────────
$mutatigActions = ['toggle'];
if (in_array($action, $mutatigActions, true) && !check_bitrix_sessid()) {
    jsonResponse(['success' => false, 'message' => 'CSRF-проверка не пройдена']);
}

// ── Сессионный fallback (для гостей) ─────────────────────────────────────────
if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// ── HL-блок: получить entityDataClass ────────────────────────────────────────
$getEntityClass = static function (): ?string {
    static $cls = null;
    if ($cls !== null) {
        return $cls;
    }

    if (!Loader::includeModule('highloadblock')) {
        return null;
    }

    $hlBlock = HighloadBlockTable::getList([
        'filter' => ['=NAME' => 'Wishlist'],
    ])->fetch();

    if (!$hlBlock) {
        return null;
    }

    $cls = HighloadBlockTable::compileEntity($hlBlock)->getDataClass();
    return $cls;
};

// ── Вспомогательные функции (в неймспейсе файла через замыкания) ──────────────

/** Список ID товаров из HL-блока для пользователя */
$hlGetProductIds = static function (string $cls, int $userId): array {
    $ids = [];
    $res = $cls::getList([
        'select' => ['UF_PRODUCT_ID'],
        'filter' => ['=UF_USER_ID' => $userId],
    ]);
    while ($row = $res->fetch()) {
        $ids[] = (int)$row['UF_PRODUCT_ID'];
    }
    return $ids;
};

/** Найти запись в HL-блоке */
$hlFindRow = static function (string $cls, int $userId, int $productId): ?array {
    return $cls::getList([
        'filter' => [
            '=UF_USER_ID'    => $userId,
            '=UF_PRODUCT_ID' => $productId,
        ],
        'limit' => 1,
    ])->fetch() ?: null;
};

// ── ACTION: list ──────────────────────────────────────────────────────────────
if ($action === 'list') {
    if (!$isAuth) {
        jsonResponse([
            'success'     => true,
            'product_ids' => array_values($_SESSION['wishlist']),
            'count'       => count($_SESSION['wishlist']),
        ]);
    }

    $cls = $getEntityClass();
    if (!$cls) {
        jsonResponse(['success' => false, 'message' => 'HL-блок Wishlist не найден']);
    }

    $ids = $hlGetProductIds($cls, $userId);
    jsonResponse([
        'success'     => true,
        'product_ids' => $ids,
        'count'       => count($ids),
    ]);
}

// ── ACTION: check ─────────────────────────────────────────────────────────────
if ($action === 'check') {
    if (!$productId) {
        jsonResponse(['success' => false, 'message' => 'productId обязателен']);
    }

    if (!$isAuth) {
        $inList = in_array($productId, $_SESSION['wishlist'], true);
        jsonResponse(['success' => true, 'in_wishlist' => $inList]);
    }

    $cls = $getEntityClass();
    if (!$cls) {
        jsonResponse(['success' => false, 'message' => 'HL-блок Wishlist не найден']);
    }

    jsonResponse([
        'success'     => true,
        'in_wishlist' => (bool)$hlFindRow($cls, $userId, $productId),
    ]);
}

// ── ACTION: toggle ────────────────────────────────────────────────────────────
if ($action === 'toggle') {
    if (!$productId) {
        jsonResponse(['success' => false, 'message' => 'productId обязателен']);
    }

    // ── Гость — работаем с сессией ────────────────────────────────────────────
    if (!$isAuth) {
        $pos = array_search($productId, $_SESSION['wishlist'], true);

        if ($pos !== false) {
            array_splice($_SESSION['wishlist'], $pos, 1);
            $actionDone = 'removed';
        } else {
            $_SESSION['wishlist'][] = $productId;
            $actionDone = 'added';
        }

        jsonResponse([
            'success'     => true,
            'action'      => $actionDone,
            'count'       => count($_SESSION['wishlist']),
            'product_ids' => array_values($_SESSION['wishlist']),
        ]);
    }

    // ── Авторизован — работаем с HL-блоком ───────────────────────────────────
    $cls = $getEntityClass();
    if (!$cls) {
        jsonResponse(['success' => false, 'message' => 'HL-блок Wishlist не найден']);
    }

    $existing = $hlFindRow($cls, $userId, $productId);

    if ($existing) {
        $result = $cls::delete((int)$existing['ID']);
        if (!$result->isSuccess()) {
            jsonResponse([
                'success' => false,
                'message' => implode(', ', $result->getErrorMessages()),
            ]);
        }
        $actionDone = 'removed';
    } else {
        $result = $cls::add([
            'UF_USER_ID'    => $userId,
            'UF_PRODUCT_ID' => $productId,
            'UF_DATE_ADD'   => new \Bitrix\Main\Type\DateTime(),
        ]);
        if (!$result->isSuccess()) {
            jsonResponse([
                'success' => false,
                'message' => implode(', ', $result->getErrorMessages()),
            ]);
        }
        $actionDone = 'added';
    }

    $ids = $hlGetProductIds($cls, $userId);
    jsonResponse([
        'success'     => true,
        'action'      => $actionDone,
        'count'       => count($ids),
        'product_ids' => $ids,
    ]);
}

// ── Неизвестное действие ──────────────────────────────────────────────────────
jsonResponse(['success' => false, 'message' => 'Неизвестное действие: ' . htmlspecialchars($action)]);
