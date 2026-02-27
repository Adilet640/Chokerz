<?php
/**
 * Пагинация результатов поиска CHOKERZ
 * Переменные $paginationData и $paginationSection обязательны
 * Desktop: классическая + «Загрузить ещё»
 * Mobile: только «Загрузить ещё» (управляется через CSS)
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array  $paginationData    — массив PAGINATION[PRODUCTS|INFO] */
/** @var string $paginationSection — 'products' | 'info' */
/** @var callable $buildUrl */

if (empty($paginationData) || (int)$paginationData['TOTAL_PAGES'] <= 1) {
    return;
}

$currentPage = (int)$paginationData['CURRENT'];
$totalPages  = (int)$paginationData['TOTAL_PAGES'];
$hasNext     = (bool)$paginationData['HAS_NEXT'];
$nextPage    = (int)$paginationData['NEXT_PAGE'];
$totalItems  = (int)$paginationData['TOTAL_ITEMS'];
?>

<nav
    class="search-pagination search-pagination--<?= $paginationSection ?>"
    aria-label="Страницы результатов"
    data-pagination-section="<?= $paginationSection ?>"
>

    <!-- Классическая пагинация (SEO + Desktop) -->
    <div class="search-pagination__pages" data-pagination-pages>
        <?php if ($currentPage > 1): ?>
        <a
            href="<?= $buildUrl(['page' => $currentPage - 1]) ?>"
            class="search-pagination__arrow"
            rel="prev"
            aria-label="Предыдущая страница"
        >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </a>
        <?php endif; ?>

        <?php
        // Показываем: 1, ..., N-1, N, N+1, ..., LAST (скользящее окно ±2)
        $window = 2;
        for ($p = 1; $p <= $totalPages; $p++):
            $inWindow = abs($p - $currentPage) <= $window;
            $isEdge   = $p === 1 || $p === $totalPages;

            if (!$inWindow && !$isEdge) {
                if ($p === 2 || $p === $totalPages - 1) {
                    echo '<span class="search-pagination__dots" aria-hidden="true">…</span>';
                }
                continue;
            }
        ?>
        <a
            href="<?= $buildUrl(['page' => $p]) ?>"
            class="search-pagination__page<?= $p === $currentPage ? ' search-pagination__page--active' : '' ?>"
            <?= $p === $currentPage ? 'aria-current="page"' : '' ?>
        ><?= $p ?></a>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
        <a
            href="<?= $buildUrl(['page' => $currentPage + 1]) ?>"
            class="search-pagination__arrow"
            rel="next"
            aria-label="Следующая страница"
        >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </a>
        <?php endif; ?>
    </div>

    <!-- Кнопка «Загрузить ещё» (содержит <a href> для SEO) -->
    <?php if ($hasNext): ?>
    <div class="search-pagination__load-more">
        <a
            href="<?= $buildUrl(['page' => $nextPage]) ?>"
            class="btn btn--load-more search-pagination__load-more-btn"
            data-load-more
            data-section="<?= $paginationSection ?>"
            data-page="<?= $nextPage ?>"
            data-total-pages="<?= $totalPages ?>"
        >
            Загрузить ещё
            <span class="search-pagination__load-more-count">
                (осталось <?= $totalItems - ($currentPage * ($totalItems / $totalPages)) ?>)
            </span>
        </a>
    </div>
    <?php endif; ?>

</nav>
