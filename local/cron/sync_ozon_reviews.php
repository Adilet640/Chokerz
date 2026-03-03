<?php
/**
 * sync_ozon_reviews.php — синхронизация отзывов с OZON Seller API v3
 *
 * Запуск: php /local/cron/sync_ozon_reviews.php
 * Агент:  ChokerzCronHandlers::syncOzonReviews()
 *
 * Путь: local/cron/sync_ozon_reviews.php
 *
 * Изменения (2026-03-03):
 *  - CIBlockElement::GetList        → ElementTable + ReferenceField на ElementPropertyTable (D7)
 *  - CIBlockElement::SetPropertyValuesEx → ElementPropertyTable::add/update (D7)
 *  - CEventLog::Add                 → Bitrix\Main\EventLog\EventLogTable::add (D7)
 *  - $_SERVER['DOCUMENT_ROOT'] определяется надёжно: сначала из окружения, затем realpath
 *  - $hlClass кэшируется в свойстве — лишний запрос к БД в updateRatings() устранён
 *
 * @package CHOKERZ
 * @version 1.2
 */

// ─── Окружение Битрикса ────────────────────────────────────────────────────
// DOCUMENT_ROOT задаётся в crontab через переменную окружения.
// Если не задана — вычисляем через realpath (local/cron/ → корень сайта).
if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../..');
}

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_CRONTAB_SUPPORT', true);
define('check_server_name', false);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Bitrix\Main\EventLog\EventLogTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Highloadblock\HighloadBlockTable;

// ─── Класс синхронизации ───────────────────────────────────────────────────

class ChokerzCronHandlers
{
    private const MAX_REVIEWS = 1000;
    private const PAGE_LIMIT  = 100;

    /**
     * FQCN DataManager HL-блока — инициализируется один раз в run(),
     * переиспользуется в upsertReview() и updateRatings() без повторных запросов к БД.
     */
    private ?string $hlClass = null;

    /** Кэш: [ozon_product_id => bitrix_element_id|0] */
    private array $productIdCache = [];

    // ──────────────────────────────────────────────────────────────────────
    // Точка входа агента
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Вызывается агентом Битрикса.
     * Возвращает строку вызова для автоматической постановки агента в очередь.
     */
    public static function syncOzonReviews(): string
    {
        try {
            (new self())->run();
        } catch (\Throwable $e) {
            self::log('CRITICAL', $e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return '\\ChokerzCronHandlers::syncOzonReviews();';
    }

    // ──────────────────────────────────────────────────────────────────────
    // Основной процесс
    // ──────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        self::log('INFO', 'Синхронизация OZON отзывов запущена');

        $clientId = \Bitrix\Main\Config\Option::get('chokerz', 'ozon_client_id', '');
        $apiKey   = \Bitrix\Main\Config\Option::get('chokerz', 'ozon_api_key',   '');

        if (empty($clientId) || empty($apiKey)) {
            self::log('ERROR', 'OZON API credentials не настроены (chokerz.ozon_client_id / ozon_api_key)');
            return;
        }

        // Один запрос к БД за всё время жизни объекта
        $this->hlClass = $this->resolveHlClass();
        if ($this->hlClass === null) {
            self::log('ERROR', 'HL-блок "Отзывы" (Otzyvy) не найден');
            return;
        }

        $nextPageToken = '';
        $totalSynced   = 0;
        $touchedIds    = []; // elementId => true

        do {
            $response = $this->fetchReviews($clientId, $apiKey, $nextPageToken);
            if ($response === null) {
                break;
            }

            $reviews       = $response['result']['reviews']        ?? [];
            $nextPageToken = $response['result']['next_page_token'] ?? '';

            foreach ($reviews as $review) {
                if ($totalSynced >= self::MAX_REVIEWS) {
                    break 2;
                }

                $elementId = $this->resolveElementId((string)($review['product_id'] ?? ''));
                if (!$elementId) {
                    self::log('WARNING', 'Не найден элемент для OZON product_id=' . ($review['product_id'] ?? '?'));
                    continue;
                }

                $this->upsertReview($review, $elementId);
                $touchedIds[$elementId] = true;
                $totalSynced++;
            }

        } while (!empty($nextPageToken) && $totalSynced < self::MAX_REVIEWS);

        $this->updateRatings(array_keys($touchedIds));

        self::log('INFO', "Синхронизация завершена. Обработано отзывов: {$totalSynced}");
    }

    // ──────────────────────────────────────────────────────────────────────
    // OZON Seller API v3
    // ──────────────────────────────────────────────────────────────────────

    private function fetchReviews(string $clientId, string $apiKey, string $nextPageToken): ?array
    {
        $url  = 'https://api-seller.ozon.ru/v3/review/info/list';
        $body = json_encode([
            'limit'           => self::PAGE_LIMIT,
            'next_page_token' => $nextPageToken,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Client-Id: '  . $clientId,
                'Api-Key: '    . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) {
            self::log('ERROR', 'cURL error: ' . $err);
            return null;
        }
        if ($code !== 200) {
            self::log('ERROR', "OZON API HTTP {$code}: " . $raw);
            return null;
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            self::log('ERROR', 'JSON decode error: ' . json_last_error_msg());
            return null;
        }

        return $decoded;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Маппинг OZON product_id → ID элемента инфоблока — только D7
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Ищет элемент инфоблока по значению свойства ARTICLE через D7 ORM.
     * Использует ReferenceField для JOIN с таблицей значений свойств.
     * CIBlockElement не используется.
     *
     * @param string $ozonProductId  Артикул из OZON (product_id)
     * @return int|null
     */
    private function resolveElementId(string $ozonProductId): ?int
    {
        if (array_key_exists($ozonProductId, $this->productIdCache)) {
            return $this->productIdCache[$ozonProductId] ?: null;
        }

        if (!Loader::includeModule('iblock')) {
            return null;
        }

        // JOIN: ElementTable → ElementPropertyTable → PropertyTable (фильтр по CODE)
        $row = ElementTable::getList([
            'runtime' => [
                // Значение свойства
                new ReferenceField(
                    'PROP_VALUE',
                    ElementPropertyTable::class,
                    Join::on('this.ID', 'ref.IBLOCK_ELEMENT_ID'),
                    ['join_type' => 'INNER']
                ),
                // Описание свойства (нужен CODE)
                new ReferenceField(
                    'PROP_DEF',
                    PropertyTable::class,
                    Join::on('ref.ID', 'this.PROP_VALUE.IBLOCK_PROPERTY_ID'),
                    ['join_type' => 'INNER']
                ),
            ],
            'filter' => [
                '=ACTIVE'               => 'Y',
                '=PROP_DEF.CODE'        => 'ARTICLE',
                '=PROP_VALUE.VALUE_STRING' => $ozonProductId,
            ],
            'select' => ['ID'],
            'limit'  => 1,
        ])->fetch();

        $id = $row ? (int)$row['ID'] : 0;
        $this->productIdCache[$ozonProductId] = $id;

        return $id ?: null;
    }

    // ──────────────────────────────────────────────────────────────────────
    // HL-блок
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Возвращает FQCN DataManager HL-блока "Отзывы".
     * Вызывается единственный раз в run(); результат хранится в $this->hlClass.
     */
    private function resolveHlClass(): ?string
    {
        if (!Loader::includeModule('highloadblock')) {
            return null;
        }

        $hlData = HighloadBlockTable::getList([
            'filter' => ['=NAME' => 'Otzyvy'], // Технич. имя HL-блока
            'limit'  => 1,
        ])->fetch();

        if (!$hlData) {
            return null;
        }

        return HighloadBlockTable::compileEntity($hlData)->getDataClass();
    }

    /**
     * Upsert отзыва в HL-блок по UF_EXTERNAL_ID (ID отзыва в OZON).
     * Использует $this->hlClass (инициализирован в run()).
     */
    private function upsertReview(array $review, int $elementId): void
    {
        $externalId = (string)($review['id'] ?? '');
        if (empty($externalId)) {
            return;
        }

        $fields = [
            'UF_PRODUCT_ID'  => $elementId,
            'UF_RATING'      => (float)($review['rating']       ?? 0),
            'UF_TEXT'        => (string)($review['text']        ?? ''),
            'UF_AUTHOR'      => (string)($review['author_name'] ?? ''),
            'UF_DATE'        => $this->parseDate($review['published_at'] ?? ''),
            'UF_SOURCE'      => 'ozon',
            'UF_EXTERNAL_ID' => $externalId,
        ];

        $existing = ($this->hlClass)::getList([
            'filter' => ['=UF_EXTERNAL_ID' => $externalId],
            'select' => ['ID'],
            'limit'  => 1,
        ])->fetch();

        $result = $existing
            ? ($this->hlClass)::update($existing['ID'], $fields)
            : ($this->hlClass)::add($fields);

        if (!$result->isSuccess()) {
            self::log(
                'WARNING',
                "Ошибка сохранения отзыва {$externalId}: " . implode(', ', $result->getErrorMessages())
            );
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // Пересчёт рейтинга — только D7, без CIBlockElement
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Пересчитывает средний рейтинг по всем отзывам HL-блока для каждого товара
     * и сохраняет результат в свойство RATING через ElementPropertyTable (D7).
     * CIBlockElement::SetPropertyValuesEx не используется.
     *
     * @param int[] $elementIds  Затронутые в текущем запуске элементы инфоблока
     */
    private function updateRatings(array $elementIds): void
    {
        if (empty($elementIds) || $this->hlClass === null) {
            return;
        }

        if (!Loader::includeModule('iblock')) {
            return;
        }

        // ID свойства RATING — один запрос для всех товаров
        $propRow = PropertyTable::getList([
            'filter' => ['=CODE' => 'RATING', '=ACTIVE' => 'Y'],
            'select' => ['ID'],
            'limit'  => 1,
        ])->fetch();

        if (!$propRow) {
            self::log('ERROR', 'Свойство RATING не найдено ни в одном инфоблоке');
            return;
        }

        $propertyId = (int)$propRow['ID'];

        foreach ($elementIds as $elementId) {
            // Агрегируем по всем отзывам товара из HL-блока
            $res = ($this->hlClass)::getList([
                'filter' => ['=UF_PRODUCT_ID' => $elementId, '>UF_RATING' => 0],
                'select' => ['UF_RATING'],
            ]);

            $sum   = 0.0;
            $count = 0;
            while ($row = $res->fetch()) {
                $sum += (float)$row['UF_RATING'];
                $count++;
            }

            if ($count === 0) {
                continue;
            }

            $avgRating = round($sum / $count, 1);

            // D7 upsert: обновляем существующее значение или создаём новое
            $existingProp = ElementPropertyTable::getList([
                'filter' => [
                    '=IBLOCK_ELEMENT_ID'  => $elementId,
                    '=IBLOCK_PROPERTY_ID' => $propertyId,
                ],
                'select' => ['ID'],
                'limit'  => 1,
            ])->fetch();

            if ($existingProp) {
                ElementPropertyTable::update($existingProp['ID'], [
                    'VALUE'     => $avgRating,
                    'VALUE_NUM' => $avgRating,
                ]);
            } else {
                ElementPropertyTable::add([
                    'IBLOCK_ELEMENT_ID'  => $elementId,
                    'IBLOCK_PROPERTY_ID' => $propertyId,
                    'VALUE'              => $avgRating,
                    'VALUE_NUM'          => $avgRating,
                ]);
            }

            self::log('INFO', "Элемент #{$elementId}: рейтинг → {$avgRating} (на основе {$count} отзывов)");
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // Вспомогательные методы
    // ──────────────────────────────────────────────────────────────────────

    private function parseDate(string $iso): string
    {
        if (empty($iso)) {
            return date('Y-m-d H:i:s');
        }
        $ts = strtotime($iso);
        return $ts ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s');
    }

    /**
     * Логирование:
     *  - Всегда пишет в /local/logs/ozon_sync_YYYY-MM-DD.log
     *  - Для ERROR/CRITICAL — дополнительно в EventLogTable (D7, не CEventLog)
     *
     * @param string $severity  INFO | WARNING | ERROR | CRITICAL
     * @param string $message
     */
    public static function log(string $severity, string $message): void
    {
        $logDir  = $_SERVER['DOCUMENT_ROOT'] . '/local/logs';
        $logFile = $logDir . '/ozon_sync_' . date('Y-m-d') . '.log';

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        @file_put_contents(
            $logFile,
            '[' . date('Y-m-d H:i:s') . "] [{$severity}] {$message}" . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        if (in_array($severity, ['ERROR', 'CRITICAL'], true)) {
            // D7: Bitrix\Main\EventLog\EventLogTable — CEventLog не используется
            EventLogTable::add([
                'SEVERITY'      => $severity,
                'AUDIT_TYPE_ID' => 'CHOKERZ_OZON_SYNC',
                'MODULE_ID'     => 'chokerz',
                'ITEM_ID'       => '0',
                'DESCRIPTION'   => $message,
            ]);
        }
    }
}

// ─── CLI-запуск ───────────────────────────────────────────────────────────
if (php_sapi_name() === 'cli' || defined('BX_CRONTAB_SUPPORT')) {
    ChokerzCronHandlers::syncOzonReviews();
}
