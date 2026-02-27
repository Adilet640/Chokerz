<?php
/**
 * Шаблон: chokerz:catalog.sort / .default / template.php
 * Попап сортировки каталога по макету FILTER & POP UP (нижняя панель).
 *
 * Структура по макету:
 *   - Заголовок «Сортировать» + подзаголовок «Выберите порядок»
 *   - Группа «Порядок» (аккордеон, открыта по умолчанию)
 *   - Radio-варианты: По популярности / Сначала дешевле / Сначала дороже / По рейтингу
 *   - Кнопка «Применить» (полная ширина, primary)
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
$sortOptions = $arResult['SORT_OPTIONS'];
$activeSort  = $arResult['ACTIVE_SORT'];
?>

<div class="catalog-sort" id="catalog-sort" data-sort-component>

    <!-- Шапка -->
    <div class="catalog-sort__header">
        <span class="catalog-sort__title">Сортировать</span>
        <span class="catalog-sort__subtitle">Выберите порядок</span>
    </div>

    <!-- Форма -->
    <form
        class="catalog-sort__form"
        id="catalog-sort-form"
        method="get"
        action=""
        data-sort-form
    >
        <!-- Группа «Порядок» (аккордеон) -->
        <div
            class="catalog-sort__group catalog-sort__group--open"
            data-sort-group
        >
            <button
                class="catalog-sort__group-toggle"
                type="button"
                aria-expanded="true"
                aria-controls="sort-group-body"
                data-accordion-toggle
            >
                <span class="catalog-sort__group-name">Порядок</span>
                <svg
                    class="catalog-sort__group-chevron"
                    width="16"
                    height="16"
                    viewBox="0 0 16 16"
                    fill="none"
                    aria-hidden="true"
                >
                    <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <div class="catalog-sort__group-body" id="sort-group-body" role="radiogroup" aria-label="Вариант сортировки">

                <?php foreach ($sortOptions as $code => $option):
                    $isActive  = ($code === $activeSort);
                    $inputId   = 'sort-option-' . htmlspecialcharsbx($code);
                ?>
                <label
                    class="catalog-sort__option<?= $isActive ? ' catalog-sort__option--checked' : '' ?>"
                    for="<?= $inputId ?>"
                >
                    <input
                        class="catalog-sort__radio"
                        type="radio"
                        id="<?= $inputId ?>"
                        name="sort_code"
                        value="<?= htmlspecialcharsbx($code) ?>"
                        <?= $isActive ? 'checked' : '' ?>
                        data-sort-by="<?= htmlspecialcharsbx($option['field']) ?>"
                        data-sort-order="<?= htmlspecialcharsbx($option['order']) ?>"
                        data-sort-input
                    >
                    <!-- Кастомная radio-метка через CSS, без <i>/<b> -->
                    <span class="catalog-sort__radio-mark" aria-hidden="true">
                        <svg class="catalog-sort__radio-check" width="10" height="8" viewBox="0 0 10 8" fill="none" aria-hidden="true">
                            <path d="M1 4l3 3 5-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="catalog-sort__option-label"><?= htmlspecialcharsbx($option['label']) ?></span>
                </label>
                <?php endforeach; ?>

                <!-- Скрытые поля — заполняются через data-sort-by / data-sort-order в JS при выборе -->
                <input type="hidden" name="sort_by"    id="sort-hidden-by"    value="<?= htmlspecialcharsbx($arResult['CURRENT_SORT_BY']) ?>">
                <input type="hidden" name="sort_order" id="sort-hidden-order" value="<?= htmlspecialcharsbx($arResult['CURRENT_ORDER']) ?>">

            </div>
            <!-- /group-body -->

        </div>
        <!-- /group -->

        <!-- Кнопка применить -->
        <div class="catalog-sort__footer">
            <button
                class="btn btn--primary btn--full catalog-sort__btn-apply"
                type="submit"
                data-sort-apply
            >Применить</button>
        </div>

    </form>

</div>
<!-- /catalog-sort -->
