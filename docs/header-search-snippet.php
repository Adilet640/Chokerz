<?php
/**
 * Фрагмент блока поиска для header.php CHOKERZ
 * Заменяет существующий <div class="header__search search"> в header.php
 *
 * Изменения относительно текущего header.php:
 * - action изменён с /search/ на /search/ (уже верно)
 * - Добавлен data-header-search на контейнер
 * - Добавлен data-search-input на <input>
 * - Добавлен data-search-suggest на контейнер выпадающих подсказок
 * - Добавлена кнопка сброса data-search-clear
 * - Сохранён текущий запрос из $_GET['q'] если страница поиска
 *
 * ИНСТРУКЦИЯ: Заменить блок <!-- Поиск --> в /local/templates/chokerz/header.php
 */

// Текущий запрос — подсвечивается в строке при нахождении на странице поиска
$headerSearchQuery = '';
if (str_starts_with($_SERVER['REQUEST_URI'], '/search/')) {
    $headerSearchQuery = htmlspecialcharsEx(trim($_GET['q'] ?? ''));
}
?>

<!-- Поиск -->
<div class="header__search search" data-header-search>
    <form action="/search/" method="get" class="search__form" role="search" data-search-form>
        <input
            type="search"
            name="q"
            value="<?= $headerSearchQuery ?>"
            placeholder="Поиск товаров..."
            class="search__input"
            autocomplete="off"
            aria-label="Поиск по сайту"
            data-search-input
        >
        <?php if ($headerSearchQuery !== ''): ?>
        <button type="button" class="search__clear" aria-label="Очистить" data-search-clear>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
        <?php endif; ?>
        <button type="submit" class="search__btn" aria-label="Найти">
            <svg class="search__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
        </button>
    </form>

    <!-- Выпадающие подсказки (заполняются JS через /local/ajax/search-suggest.php) -->
    <div class="search__suggest search-suggest" aria-live="polite" data-search-suggest hidden>
        <div class="search-suggest__products" data-suggest-products></div>
        <div class="search-suggest__info" data-suggest-info></div>
        <a href="#" class="search-suggest__all" data-suggest-all-link hidden>
            Все результаты
        </a>
    </div>
</div>
