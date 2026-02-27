<?php
/**
 * Шаблон: bitrix:catalog / chokerz / template.php
 *
 * Оркестрирует вывод каталога:
 *   - На desktop: боковая панель с фильтром + панель сортировки
 *   - На mobile: кнопки «Фильтры» / «Сортировать» открывают modal-drawer через JS
 *
 * Компоненты вызываются через $APPLICATION->IncludeComponent() — стандартное API.
 * catalog.smart.filter рендерится с шаблоном 'chokerz'.
 * chokerz:catalog.sort рендерится с шаблоном '.default'.
 *
 * Счётчик активных фильтров на мобильной кнопке обновляется через JS
 * при событии data-filter-applied (без inline-скриптов).
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var CMain $APPLICATION */

$sectionId    = (int)($arResult['SECTION']['ID'] ?? 0);
$sectionCode  = htmlspecialcharsbx($arResult['SECTION']['CODE'] ?? '');
$iblockId     = (int)($arParams['IBLOCK_ID'] ?? 0);
$filterPreset = $arParams['FILTER_NAME'] ?? 'arrFilter';
?>

<div class="catalog-page" data-catalog-page>

    <!-- =====================================================
         MOBILE: Кнопки открытия попапов фильтра/сортировки
         Отображаются только на мобильном через CSS
         ===================================================== -->
    <div class="catalog-page__mobile-toolbar" aria-label="Инструменты каталога">

        <button
            class="catalog-toolbar__btn catalog-toolbar__btn--filter"
            type="button"
            data-modal-open="filter-popup"
            aria-controls="catalog-filter-popup"
            aria-expanded="false"
        >
            <svg class="catalog-toolbar__icon" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M3 5h14M6 10h8M9 15h2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            <span class="catalog-toolbar__label">Фильтры</span>
            <!-- Счётчик активных фильтров (заполняется JS) -->
            <span
                class="catalog-toolbar__badge"
                id="filter-mobile-badge"
                aria-live="polite"
                hidden
            ></span>
        </button>

        <button
            class="catalog-toolbar__btn catalog-toolbar__btn--sort"
            type="button"
            data-modal-open="sort-popup"
            aria-controls="catalog-sort-popup"
            aria-expanded="false"
        >
            <svg class="catalog-toolbar__icon" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M3 5h14M5 10h10M8 15h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            <span class="catalog-toolbar__label">Сортировать</span>
        </button>

    </div>
    <!-- /mobile-toolbar -->

    <div class="catalog-page__layout">

        <!-- =====================================================
             DESKTOP: Боковая панель (sidebar)
             На mobile скрыта через CSS, показывается в drawer
             ===================================================== -->
        <aside class="catalog-page__sidebar" aria-label="Фильтры и сортировка">

            <!-- Фильтр — стандартный компонент Битрикс с кастомным шаблоном -->
            <?php
            $APPLICATION->IncludeComponent(
                'bitrix:catalog.smart.filter',
                'chokerz',
                [
                    'IBLOCK_ID'          => $iblockId,
                    'SECTION_ID'         => $sectionId,
                    'SECTION_CODE'       => $sectionCode,
                    'FILTER_NAME'        => $filterPreset,
                    'PRICE_CODE'         => ['BASE'],
                    'HIDE_NOT_AVAILABLE' => 'N',
                    'AJAX_MODE'          => 'Y',
                    'AJAX_OPTION_JUMP'   => 'N',
                    'AJAX_OPTION_STYLE'  => 'Y',
                    'SEF_MODE'           => 'N',
                    'URL_TEMPLATES'      => [],
                ],
                $component
            );
            ?>

            <!-- Сортировка — кастомный компонент -->
            <?php
            $APPLICATION->IncludeComponent(
                'chokerz:catalog.sort',
                '.default',
                [
                    'VISIBLE_SORTS' => ['popular', 'price-asc', 'price-desc', 'rating', 'date'],
                ],
                $component
            );
            ?>

        </aside>
        <!-- /sidebar -->

        <!-- =====================================================
             ОСНОВНОЕ СОДЕРЖИМОЕ: список товаров
             ===================================================== -->
        <main class="catalog-page__content" id="catalog-items-container" aria-label="Список товаров">

            <!-- Список товаров подключается в родительском шаблоне раздела -->
            <?php
            // Шаблон списка товаров (catalog.section) подключается в шаблоне раздела.
            // Здесь — placeholder для корректной вёрстки каркаса страницы.
            // Реальный вызов catalog.section выполняется в index.php раздела /catalog/.
            ?>

        </main>

    </div>
    <!-- /layout -->

</div>
<!-- /catalog-page -->

<!-- =====================================================
     MOBILE DRAWERS (попапы фильтра и сортировки)
     Рендерятся вне layout, управляются через JS modal-manager
     ===================================================== -->

<!-- Drawer: Фильтры -->
<div
    class="catalog-drawer catalog-drawer--filter"
    id="catalog-filter-popup"
    role="dialog"
    aria-modal="true"
    aria-label="Фильтры"
    hidden
    data-drawer="filter-popup"
>
    <div class="catalog-drawer__overlay" data-drawer-close></div>
    <div class="catalog-drawer__panel">
        <div class="catalog-drawer__handle" aria-hidden="true"></div>
        <!-- Фильтр вставляется через JS (clone) из боковой панели -->
        <div class="catalog-drawer__content" data-drawer-filter-content></div>
    </div>
</div>

<!-- Drawer: Сортировка -->
<div
    class="catalog-drawer catalog-drawer--sort"
    id="catalog-sort-popup"
    role="dialog"
    aria-modal="true"
    aria-label="Сортировка"
    hidden
    data-drawer="sort-popup"
>
    <div class="catalog-drawer__overlay" data-drawer-close></div>
    <div class="catalog-drawer__panel">
        <div class="catalog-drawer__handle" aria-hidden="true"></div>
        <div class="catalog-drawer__content" data-drawer-sort-content></div>
    </div>
</div>
