<?php
/**
 * Шаблон компонента blog.list — список материалов блога CHOKERZ
 * Десктоп: сетка 2 колонки, табы (Все / Статьи / Видео), фильтр по категории
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */
$items      = $arResult['ITEMS']      ?? [];
$pagination = $arResult['PAGINATION'] ?? [];
$sections   = $arResult['SECTIONS']   ?? [];
$filter     = $arResult['FILTER']     ?? [];

$currentType    = $filter['TYPE']    ?? 'all';
$currentSection = (int)($filter['SECTION'] ?? 0);
$currentTag     = $filter['TAG']     ?? '';
$currentPage    = (int)($filter['PAGE'] ?? 1);

/**
 * Формирует URL фильтра, сохраняя остальные параметры
 */
$buildUrl = static function (array $params): string {
    $base = strtok($_SERVER['REQUEST_URI'], '?');
    $query = array_merge($_GET, $params);
    // Сбрасываем page при смене фильтра
    if (isset($params['type']) || isset($params['section']) || isset($params['tag'])) {
        unset($query['page']);
    }
    $query = array_filter($query, static fn($v) => $v !== '' && $v !== '0' && $v !== 0);
    return $base . ($query ? '?' . http_build_query($query) : '');
};

$tabs = [
    ['key' => 'all',     'label' => 'Все'],
    ['key' => 'article', 'label' => 'Статьи'],
    ['key' => 'video',   'label' => 'Видео'],
];
?>

<div class="blog-list" data-blog-list>

    <!-- ШАПКА РАЗДЕЛА -->
    <div class="blog-list__header">
        <div class="blog-list__header-text">
            <span class="blog-list__eyebrow">БЛОГ</span>
            <h1 class="blog-list__title">Статьи и видео об амуниции и уходе</h1>
            <p class="blog-list__desc">Советы по выбору, уходу и тренировкам. Полезный контент для владельцев питомцев.</p>
        </div>

        <!-- Поиск по блогу -->
        <form class="blog-list__search blog-search" action="" method="get" data-blog-search-form>
            <?php foreach ($_GET as $k => $v): if ($k === 'q' || $k === 'page') continue; ?>
            <input type="hidden" name="<?= htmlspecialcharsEx($k) ?>" value="<?= htmlspecialcharsEx($v) ?>">
            <?php endforeach; ?>
            <input
                type="search"
                name="q"
                class="blog-search__input"
                placeholder="Поиск по статьям..."
                value="<?= htmlspecialcharsEx($_GET['q'] ?? '') ?>"
                autocomplete="off"
            >
            <button type="submit" class="blog-search__btn" aria-label="Найти">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
            </button>
        </form>
    </div>

    <!-- ТАБЫ: Все / Статьи / Видео -->
    <div class="blog-list__tabs blog-tabs" role="tablist" aria-label="Тип материала">
        <?php foreach ($tabs as $tab): ?>
        <a
            href="<?= $buildUrl(['type' => $tab['key']]) ?>"
            class="blog-tabs__tab<?= $currentType === $tab['key'] ? ' blog-tabs__tab--active' : '' ?>"
            role="tab"
            aria-selected="<?= $currentType === $tab['key'] ? 'true' : 'false' ?>"
            data-blog-tab="<?= $tab['key'] ?>"
        >
            <?= $tab['label'] ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- ФИЛЬТР: категория + тег -->
    <?php if (!empty($sections) || $currentTag !== ''): ?>
    <div class="blog-list__filters blog-filters">

        <?php if (!empty($sections)): ?>
        <div class="blog-filters__group">
            <a
                href="<?= $buildUrl(['section' => '']) ?>"
                class="blog-filters__btn<?= $currentSection === 0 ? ' blog-filters__btn--active' : '' ?>"
            >Все категории</a>
            <?php foreach ($sections as $sec): ?>
            <a
                href="<?= $buildUrl(['section' => $sec['ID']]) ?>"
                class="blog-filters__btn<?= $currentSection === (int)$sec['ID'] ? ' blog-filters__btn--active' : '' ?>"
            ><?= htmlspecialcharsEx($sec['NAME']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($currentTag !== ''): ?>
        <div class="blog-filters__active-tag">
            <span class="blog-filters__tag-label">Тег:</span>
            <span class="blog-filters__tag"><?= htmlspecialcharsEx($currentTag) ?></span>
            <a href="<?= $buildUrl(['tag' => '']) ?>" class="blog-filters__tag-remove" aria-label="Сбросить тег">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </a>
        </div>
        <?php endif; ?>

    </div>
    <?php endif; ?>

    <!-- ГРИД МАТЕРИАЛОВ -->
    <div class="blog-list__content">

        <!-- Секция "Материалы" -->
        <div class="blog-list__section-label blog-section-label">Материалы</div>

        <?php if (empty($items)): ?>

        <div class="blog-list__empty blog-empty">
            <p class="blog-empty__text">Материалы не найдены. Попробуйте изменить фильтр.</p>
        </div>

        <?php else: ?>

        <ul class="blog-list__grid blog-grid" data-blog-grid aria-label="Список материалов">
            <?php foreach ($items as $item): ?>
            <?php include __DIR__ . '/card.php'; ?>
            <?php endforeach; ?>
        </ul>

        <!-- ПАГИНАЦИЯ (для SEO — классические ссылки) -->
        <?php if ($pagination['TOTAL_PAGES'] > 1): ?>
        <nav class="blog-list__pagination blog-pagination" aria-label="Страницы">

            <!-- Классическая пагинация (обязательно для SEO) -->
            <div class="blog-pagination__pages blog-pagination__pages--seo" data-pagination-seo>
                <?php if ($currentPage > 1): ?>
                <a href="<?= $buildUrl(['page' => $currentPage - 1]) ?>" class="blog-pagination__arrow" rel="prev" aria-label="Предыдущая">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $pagination['TOTAL_PAGES']; $p++): ?>
                <a
                    href="<?= $buildUrl(['page' => $p]) ?>"
                    class="blog-pagination__page<?= $p === $currentPage ? ' blog-pagination__page--active' : '' ?>"
                    <?= $p === $currentPage ? 'aria-current="page"' : '' ?>
                ><?= $p ?></a>
                <?php endfor; ?>

                <?php if ($currentPage < $pagination['TOTAL_PAGES']): ?>
                <a href="<?= $buildUrl(['page' => $currentPage + 1]) ?>" class="blog-pagination__arrow" rel="next" aria-label="Следующая">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </a>
                <?php endif; ?>
            </div>

            <!-- Кнопка «Загрузить ещё» (содержит ссылку для SEO) -->
            <?php if ($pagination['HAS_NEXT']): ?>
            <div class="blog-pagination__load-more">
                <a
                    href="<?= $buildUrl(['page' => $pagination['NEXT_PAGE']]) ?>"
                    class="btn btn--load-more blog-pagination__load-more-btn"
                    data-load-more
                    data-page="<?= (int)$pagination['NEXT_PAGE'] ?>"
                    data-total-pages="<?= (int)$pagination['TOTAL_PAGES'] ?>"
                >
                    Загрузить ещё
                    <span class="blog-pagination__load-more-count">
                        (<?= max(0, $pagination['TOTAL_ITEMS'] - count($items) * $currentPage) ?>)
                    </span>
                </a>
            </div>
            <?php endif; ?>

        </nav>
        <?php endif; ?>

        <?php endif; ?>

    </div><!-- /blog-list__content -->

</div><!-- /blog-list -->
