<?php
/**
 * Шаблон поиска CHOKERZ
 * Layout: левый сайдбар (фильтр) + правая часть (табы + результаты)
 * Вкладки: Все / Товары / Информация
 * Товары не смешиваются с информацией
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */
$query      = htmlspecialcharsEx($arResult['QUERY'] ?? '');
$tab        = $arResult['TAB']     ?? 'all';
$sort       = $arResult['SORT']    ?? 'date';
$products   = $arResult['PRODUCTS'] ?? [];
$info       = $arResult['INFO']    ?? [];
$total      = $arResult['TOTAL']   ?? ['PRODUCTS' => 0, 'INFO' => 0];
$pagination = $arResult['PAGINATION'] ?? [];
$suggest    = $arResult['SUGGEST'] ?? '';
$filterProps = $arResult['FILTER_PROPS'] ?? [];

$totalAll   = $total['PRODUCTS'] + $total['INFO'];

/**
 * Сборщик URL с сохранением параметров
 */
$buildUrl = static function (array $override): string {
    $base  = '/search/';
    $query = array_merge($_GET, $override);
    $query = array_filter($query, static fn($v) => $v !== '' && $v !== null);
    return $base . ($query ? '?' . http_build_query($query) : '');
};

$tabs = [
    ['key' => 'all',      'label' => 'Все',         'count' => $totalAll],
    ['key' => 'products', 'label' => 'Товары',       'count' => $total['PRODUCTS']],
    ['key' => 'info',     'label' => 'Информация',   'count' => $total['INFO']],
];

$sortOptions = [
    'date'       => 'По дате',
    'price_asc'  => 'Цена ↑',
    'price_desc' => 'Цена ↓',
    'popularity' => 'По популярности',
    'rating'     => 'По рейтингу',
];
?>

<div class="search-page" data-search-page>

    <!-- ШАПКА ПОИСКА -->
    <div class="search-page__header">
        <span class="search-page__eyebrow">ПОИСК</span>
        <h1 class="search-page__title">
            Результаты поиска по запросу
            <?php if ($query !== ''): ?>
            <span class="search-page__query">«<?= $query ?>»</span>
            <?php endif; ?>
        </h1>

        <!-- Форма поиска (переход по Enter) -->
        <form class="search-page__form search-form" action="/search/" method="get" role="search" data-search-form>
            <div class="search-form__wrap">
                <input
                    type="search"
                    name="q"
                    class="search-form__input"
                    value="<?= $query ?>"
                    placeholder="Введите запрос..."
                    autocomplete="off"
                    aria-label="Поисковый запрос"
                    data-search-input
                >
                <?php if ($query !== ''): ?>
                <button
                    type="button"
                    class="search-form__clear"
                    aria-label="Очистить запрос"
                    data-search-clear
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
                <?php endif; ?>
                <button type="submit" class="search-form__btn btn btn--primary" aria-label="Найти">Найти</button>
            </div>
        </form>

        <!-- Suggest: исправление опечаток -->
        <?php if ($suggest !== ''): ?>
        <p class="search-page__suggest">
            Возможно, вы искали:
            <a href="<?= $buildUrl(['q' => htmlspecialcharsEx($suggest), 'tab' => $tab]) ?>" class="search-page__suggest-link">
                <?= htmlspecialcharsEx($suggest) ?>
            </a>
        </p>
        <?php endif; ?>

        <!-- Итоговое количество -->
        <?php if ($query !== ''): ?>
        <p class="search-page__count">
            Найдено: <span class="search-page__count-num"><?= $totalAll ?></span> результатов
        </p>
        <?php endif; ?>

    </div><!-- /search-page__header -->

    <!-- ОСНОВНОЙ LAYOUT -->
    <div class="search-page__layout">

        <!-- ======== САЙДБАР: фильтры (только для вкладки Товары / Все) ======== -->
        <?php if (in_array($tab, ['all', 'products'], true) && !empty($filterProps)): ?>
        <aside class="search-page__sidebar search-filter" aria-label="Фильтр результатов">

            <div class="search-filter__title">Фильтр</div>

            <form class="search-filter__form" action="/search/" method="get" data-filter-form>
                <!-- Сохраняем q и tab -->
                <input type="hidden" name="q" value="<?= $query ?>">
                <input type="hidden" name="tab" value="<?= htmlspecialcharsEx($tab) ?>">

                <!-- Цена -->
                <div class="search-filter__group">
                    <div class="search-filter__group-title">Цена</div>
                    <div class="search-filter__price-range">
                        <input
                            type="number"
                            name="price_min"
                            class="search-filter__price-input"
                            placeholder="от"
                            value="<?= (int)($_GET['price_min'] ?? 0) ?: '' ?>"
                            min="0"
                        >
                        <span class="search-filter__price-sep">—</span>
                        <input
                            type="number"
                            name="price_max"
                            class="search-filter__price-input"
                            placeholder="до"
                            value="<?= (int)($_GET['price_max'] ?? 0) ?: '' ?>"
                            min="0"
                        >
                    </div>
                </div>

                <!-- Динамические свойства (Цвет, Материал, Размер) -->
                <?php foreach ($filterProps as $propCode => $prop): ?>
                <div class="search-filter__group" data-filter-group="<?= htmlspecialcharsEx($propCode) ?>">
                    <div class="search-filter__group-title"><?= htmlspecialcharsEx($prop['NAME']) ?></div>

                    <?php if ($propCode === 'COLOR'): ?>
                    <!-- Цвет — кружки -->
                    <div class="search-filter__colors">
                        <?php foreach ($prop['VALUES'] as $val): ?>
                        <?php
                        $paramKey    = 'filter_' . strtolower($propCode);
                        $activeVals  = (array)($_GET[$paramKey] ?? []);
                        $isActive    = in_array($val, $activeVals, true);
                        ?>
                        <label class="search-filter__color-label" title="<?= htmlspecialcharsEx($val) ?>">
                            <input
                                type="checkbox"
                                name="<?= $paramKey ?>[]"
                                value="<?= htmlspecialcharsEx($val) ?>"
                                class="search-filter__color-input"
                                <?= $isActive ? 'checked' : '' ?>
                            >
                            <span
                                class="search-filter__color-swatch"
                                style="background-color: <?= htmlspecialcharsEx($val) ?>"
                                aria-label="<?= htmlspecialcharsEx($val) ?>"
                            ></span>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <?php else: ?>
                    <!-- Остальные свойства — чекбоксы -->
                    <ul class="search-filter__check-list">
                        <?php foreach ($prop['VALUES'] as $val): ?>
                        <?php
                        $paramKey   = 'filter_' . strtolower($propCode);
                        $activeVals = (array)($_GET[$paramKey] ?? []);
                        $isActive   = in_array($val, $activeVals, true);
                        ?>
                        <li class="search-filter__check-item">
                            <label class="search-filter__check-label">
                                <input
                                    type="checkbox"
                                    name="<?= $paramKey ?>[]"
                                    value="<?= htmlspecialcharsEx($val) ?>"
                                    class="search-filter__check-input"
                                    <?= $isActive ? 'checked' : '' ?>
                                >
                                <span class="search-filter__check-text"><?= htmlspecialcharsEx($val) ?></span>
                            </label>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                </div>
                <?php endforeach; ?>

                <div class="search-filter__actions">
                    <button type="submit" class="btn btn--primary search-filter__apply">Применить</button>
                    <a href="/search/?q=<?= urlencode($arResult['QUERY']) ?>&tab=<?= $tab ?>" class="btn btn--ghost search-filter__reset">
                        Сбросить
                    </a>
                </div>
            </form>

        </aside>
        <?php endif; ?>

        <!-- ======== ОСНОВНОЙ КОНТЕНТ ======== -->
        <div class="search-page__content">

            <!-- ТАБЫ -->
            <div class="search-page__tabs search-tabs" role="tablist" aria-label="Тип результатов">
                <?php foreach ($tabs as $t): ?>
                <a
                    href="<?= $buildUrl(['tab' => $t['key'], 'page' => 1]) ?>"
                    class="search-tabs__tab<?= $tab === $t['key'] ? ' search-tabs__tab--active' : '' ?>"
                    role="tab"
                    aria-selected="<?= $tab === $t['key'] ? 'true' : 'false' ?>"
                    data-search-tab="<?= $t['key'] ?>"
                >
                    <?= $t['label'] ?>
                    <?php if ($t['count'] > 0): ?>
                    <span class="search-tabs__count"><?= $t['count'] ?></span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Пустой запрос -->
            <?php if ($query === ''): ?>
            <div class="search-page__empty">
                <p class="search-page__empty-text">Введите запрос в строку поиска.</p>
            </div>

            <!-- Нет результатов -->
            <?php elseif ($totalAll === 0): ?>
            <div class="search-page__empty">
                <p class="search-page__empty-text">По запросу «<?= $query ?>» ничего не найдено.</p>
                <?php if ($suggest !== ''): ?>
                <p class="search-page__empty-suggest">
                    Попробуйте:
                    <a href="<?= $buildUrl(['q' => htmlspecialcharsEx($suggest)]) ?>" class="search-page__suggest-link">
                        <?= htmlspecialcharsEx($suggest) ?>
                    </a>
                </p>
                <?php endif; ?>
                <a href="/catalog/" class="btn btn--outline search-page__catalog-btn">Перейти в каталог</a>
            </div>

            <?php else: ?>

            <!-- ====== ТОВАРЫ ====== -->
            <?php if (in_array($tab, ['all', 'products'], true) && !empty($products)): ?>
            <div class="search-page__section" data-search-section="products">

                <div class="search-section-head">
                    <span class="search-section-head__title">Товары</span>
                    <span class="search-section-head__count"><?= $total['PRODUCTS'] ?></span>

                    <!-- Сортировка — только на вкладке Товары -->
                    <?php if ($tab === 'products'): ?>
                    <form class="search-section-head__sort" action="/search/" method="get" data-sort-form>
                        <input type="hidden" name="q" value="<?= $query ?>">
                        <input type="hidden" name="tab" value="products">
                        <?php foreach ($_GET as $k => $v): if ($k === 'sort') continue; ?>
                        <input type="hidden" name="<?= htmlspecialcharsEx($k) ?>" value="<?= htmlspecialcharsEx(is_array($v) ? implode(',', $v) : $v) ?>">
                        <?php endforeach; ?>
                        <label for="search-sort" class="search-section-head__sort-label">Сортировка:</label>
                        <select
                            id="search-sort"
                            name="sort"
                            class="search-section-head__sort-select"
                            data-auto-submit
                        >
                            <?php foreach ($sortOptions as $val => $label): ?>
                            <option value="<?= $val ?>"<?= $sort === $val ? ' selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <?php endif; ?>
                </div>

                <ul class="search-products search-products__grid" data-products-grid aria-label="Товары по запросу">
                    <?php foreach ($products as $product): ?>
                    <li class="search-products__item">
                        <?php include __DIR__ . '/product-card.php'; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <?php include __DIR__ . '/pagination.php'; $paginationData = $pagination['PRODUCTS'] ?? []; $paginationSection = 'products'; ?>

                <!-- Ссылка «Все товары» на вкладке Все -->
                <?php if ($tab === 'all' && $total['PRODUCTS'] > count($products)): ?>
                <a href="<?= $buildUrl(['tab' => 'products']) ?>" class="btn btn--outline search-page__see-all">
                    Все товары (<?= $total['PRODUCTS'] ?>)
                </a>
                <?php endif; ?>

            </div>
            <?php endif; ?>

            <!-- ====== ИНФОРМАЦИЯ ====== -->
            <?php if (in_array($tab, ['all', 'info'], true) && !empty($info)): ?>
            <div class="search-page__section" data-search-section="info">

                <div class="search-section-head">
                    <span class="search-section-head__title">Информация</span>
                    <span class="search-section-head__count"><?= $total['INFO'] ?></span>
                </div>

                <div class="search-info search-info__grid">
                    <?php foreach ($info as $infoItem): ?>
                    <?php include __DIR__ . '/info-card.php'; ?>
                    <?php endforeach; ?>
                </div>

                <?php
                $paginationData    = $pagination['INFO'] ?? [];
                $paginationSection = 'info';
                include __DIR__ . '/pagination.php';
                ?>

                <?php if ($tab === 'all' && $total['INFO'] > count($info)): ?>
                <a href="<?= $buildUrl(['tab' => 'info']) ?>" class="btn btn--outline search-page__see-all">
                    Все материалы (<?= $total['INFO'] ?>)
                </a>
                <?php endif; ?>

            </div>
            <?php endif; ?>

            <?php endif; // totalAll > 0 ?>

        </div><!-- /search-page__content -->

    </div><!-- /search-page__layout -->

</div><!-- /search-page -->
