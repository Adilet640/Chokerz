<?php
/**
 * Шаблон: bitrix:catalog.smart.filter / chokerz / template.php
 *
 * Кастомный вывод умного фильтра каталога CHOKERZ.
 * Особенности по ТЗ:
 *   - Цвет — кастомный вывод (кружки)
 *   - Мобильное сворачивание групп (аккордеон)
 *   - Счётчик активных фильтров «Выбрано: N»
 *   - Кнопки «Сбросить» / «Применить»
 *   - BEM-нейминг, SVG-иконки, без jQuery, без inline-скриптов
 *
 * Данные из $arResult['FILTERS']:
 *   ['FILTER_ID']   — символьный код группы
 *   ['NAME']        — название группы
 *   ['TYPE']        — тип: 'checkbox', 'range', 'number', 'radio'
 *   ['VALUES']      — доступные значения (для checkbox/radio)
 *   ['VALUE']       — текущее значение (для range)
 *   ['VALUE_FROM']  — от (для range)
 *   ['VALUE_TO']    — до (для range)
 *   ['FIELD_NAME']  — имя поля формы
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $arResult */
/** @var array $arParams */
/** @var CBitrixComponent $this */

// Маппинг символьных кодов к читаемым меткам (переопределяет стандартное NAME при желании)
// Совпадает с именами свойств инфоблока каталога (см. ТЗ п. 7.1)
$filterCodeLabels = [
    'SECTION'           => 'Категория',
    'CATALOG_TYPE'      => 'Тип изделия',
    'PROPERTY_MATERIAL' => 'Материал',
    'PROPERTY_PURPOSE'  => 'Назначение',
    'PROPERTY_SIZE'     => 'Размер изделия',
    'PROPERTY_COLOR'    => 'Цвет',
    'PROPERTY_NECK'     => 'Размер / обхват шеи',
    'CATALOG_AVAILABLE' => 'Наличие',
    'PROPERTY_PICKS'    => 'Подборки',
];

// Группы, развёрнутые по умолчанию (первая = Категория)
$defaultOpenGroups = ['SECTION'];

// Подсчёт активных фильтров
$selectedCount = 0;
if (!empty($arResult['FILTERS'])) {
    foreach ($arResult['FILTERS'] as $filter) {
        if (!empty($filter['VALUES'])) {
            foreach ($filter['VALUES'] as $val) {
                if (!empty($val['CHECKED'])) {
                    $selectedCount++;
                }
            }
        } elseif (!empty($filter['VALUE_FROM']) || !empty($filter['VALUE_TO'])) {
            $selectedCount++;
        }
    }
}

// Текущий URL страницы для action формы
$currentPage = htmlspecialcharsbx($arResult['FILTER_URL'] ?? '/catalog/');
?>

<div
    class="catalog-filter"
    id="catalog-filter"
    data-filter-component
    data-filter-url="<?= $currentPage ?>"
>

    <!-- Шапка панели фильтров -->
    <div class="catalog-filter__header">
        <span class="catalog-filter__title">Фильтры</span>
        <?php if ($selectedCount > 0): ?>
        <span
            class="catalog-filter__counter"
            id="filter-selected-count"
            aria-live="polite"
            aria-label="Выбрано фильтров: <?= $selectedCount ?>"
        >Выбрано: <?= $selectedCount ?></span>
        <?php else: ?>
        <span
            class="catalog-filter__counter catalog-filter__counter--empty"
            id="filter-selected-count"
            aria-live="polite"
            hidden
        ></span>
        <?php endif; ?>
    </div>

    <!-- Форма фильтра -->
    <form
        class="catalog-filter__form"
        id="<?= htmlspecialcharsbx($arResult['FORM_ID'] ?? 'smart-filter-form') ?>"
        method="get"
        action="<?= $currentPage ?>"
        novalidate
        data-smart-filter-form
    >
        <!-- Скрытое поле сессии -->
        <?= bitrix_sessid_post() ?>

        <?php if (!empty($arResult['FILTERS'])): ?>

        <!-- Группы фильтров (аккордеон) -->
        <div class="catalog-filter__groups" role="list">

            <?php foreach ($arResult['FILTERS'] as $filterId => $filter):

                $filterCode  = (string)($filter['FILTER_ID'] ?? $filterId);
                $filterName  = $filterCodeLabels[$filterCode] ?? htmlspecialcharsbx($filter['NAME'] ?? '');
                $filterType  = (string)($filter['TYPE'] ?? 'checkbox');
                $fieldName   = htmlspecialcharsbx($filter['FIELD_NAME'] ?? '');
                $isOpen      = in_array($filterCode, $defaultOpenGroups, true);
                $isColorFilter = ($filterCode === 'PROPERTY_COLOR');

                // Флаг: есть ли активные значения в группе
                $groupHasActive = false;
                if (!empty($filter['VALUES'])) {
                    foreach ($filter['VALUES'] as $val) {
                        if (!empty($val['CHECKED'])) {
                            $groupHasActive = true;
                            break;
                        }
                    }
                }

                $groupId = 'filter-group-' . htmlspecialcharsbx($filterCode);
            ?>

            <div
                class="catalog-filter__group<?= $isOpen ? ' catalog-filter__group--open' : '' ?><?= $groupHasActive ? ' catalog-filter__group--active' : '' ?>"
                id="<?= $groupId ?>"
                role="listitem"
                data-filter-group="<?= htmlspecialcharsbx($filterCode) ?>"
            >
                <!-- Заголовок группы (триггер аккордеона) -->
                <button
                    class="catalog-filter__group-toggle"
                    type="button"
                    aria-expanded="<?= $isOpen ? 'true' : 'false' ?>"
                    aria-controls="<?= $groupId ?>-body"
                    data-accordion-toggle
                >
                    <span class="catalog-filter__group-name"><?= $filterName ?></span>
                    <svg
                        class="catalog-filter__group-chevron"
                        width="16"
                        height="16"
                        viewBox="0 0 16 16"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <!-- Тело группы (сворачиваемый блок) -->
                <div
                    class="catalog-filter__group-body"
                    id="<?= $groupId ?>-body"
                    <?= $isOpen ? '' : 'hidden' ?>
                >

                    <?php
                    // =========================================================
                    // ВЫВОД: ЦВЕТ — кастомные кружки (ТЗ п. 7.1)
                    // =========================================================
                    if ($isColorFilter && !empty($filter['VALUES'])):
                    ?>

                    <div class="catalog-filter__color-list" role="group" aria-label="Выберите цвет">
                        <?php foreach ($filter['VALUES'] as $valueId => $value):
                            if (empty($value['VALUE'])) continue;
                            $valueHex   = htmlspecialcharsbx((string)($value['HTML_VALUE'] ?? $value['VALUE'] ?? '#ccc'));
                            $valueName  = htmlspecialcharsbx((string)($value['DISPLAY_VALUE'] ?? $value['VALUE'] ?? ''));
                            $isChecked  = !empty($value['CHECKED']);
                            $inputId    = 'filter-color-' . htmlspecialcharsbx((string)$valueId);
                            $inputName  = $fieldName . '[' . htmlspecialcharsbx((string)$valueId) . ']';
                        ?>
                        <label
                            class="catalog-filter__color-item<?= $isChecked ? ' catalog-filter__color-item--checked' : '' ?>"
                            for="<?= $inputId ?>"
                            title="<?= $valueName ?>"
                            aria-label="<?= $valueName ?>"
                        >
                            <input
                                class="catalog-filter__color-input"
                                type="checkbox"
                                id="<?= $inputId ?>"
                                name="<?= $inputName ?>"
                                value="<?= htmlspecialcharsbx((string)($value['VALUE'] ?? '')) ?>"
                                <?= $isChecked ? 'checked' : '' ?>
                                data-filter-input
                                data-filter-code="<?= htmlspecialcharsbx($filterCode) ?>"
                            >
                            <span
                                class="catalog-filter__color-swatch"
                                style="--color-value: <?= $valueHex ?>;"
                                aria-hidden="true"
                            ></span>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <?php

                    // =========================================================
                    // ВЫВОД: ДИАПАЗОН ЦЕН / чисел (range)
                    // =========================================================
                    elseif (in_array($filterType, ['range', 'number'], true)):
                        $fieldFrom = htmlspecialcharsbx($filter['FIELD_NAME'] . '_FROM');
                        $fieldTo   = htmlspecialcharsbx($filter['FIELD_NAME'] . '_TO');
                        $valueFrom = htmlspecialcharsbx((string)($filter['VALUE_FROM'] ?? ''));
                        $valueTo   = htmlspecialcharsbx((string)($filter['VALUE_TO'] ?? ''));
                        $minVal    = htmlspecialcharsbx((string)($filter['MIN'] ?? '0'));
                        $maxVal    = htmlspecialcharsbx((string)($filter['MAX'] ?? '99999'));
                    ?>

                    <div class="catalog-filter__range" data-filter-range>
                        <div class="catalog-filter__range-inputs">
                            <div class="form-field form-field--inline">
                                <label class="form-field__label form-field__label--small" for="filter-from-<?= htmlspecialcharsbx($filterCode) ?>">От</label>
                                <input
                                    class="form-field__input form-field__input--small"
                                    type="number"
                                    id="filter-from-<?= htmlspecialcharsbx($filterCode) ?>"
                                    name="<?= $fieldFrom ?>"
                                    value="<?= $valueFrom ?>"
                                    min="<?= $minVal ?>"
                                    max="<?= $maxVal ?>"
                                    inputmode="numeric"
                                    data-filter-input
                                    data-filter-code="<?= htmlspecialcharsbx($filterCode) ?>"
                                    placeholder="<?= $minVal ?>"
                                >
                            </div>
                            <span class="catalog-filter__range-divider" aria-hidden="true">—</span>
                            <div class="form-field form-field--inline">
                                <label class="form-field__label form-field__label--small" for="filter-to-<?= htmlspecialcharsbx($filterCode) ?>">До</label>
                                <input
                                    class="form-field__input form-field__input--small"
                                    type="number"
                                    id="filter-to-<?= htmlspecialcharsbx($filterCode) ?>"
                                    name="<?= $fieldTo ?>"
                                    value="<?= $valueTo ?>"
                                    min="<?= $minVal ?>"
                                    max="<?= $maxVal ?>"
                                    inputmode="numeric"
                                    data-filter-input
                                    data-filter-code="<?= htmlspecialcharsbx($filterCode) ?>"
                                    placeholder="<?= $maxVal ?>"
                                >
                            </div>
                        </div>
                    </div>

                    <?php

                    // =========================================================
                    // ВЫВОД: ЧЕКБОКСЫ (стандартный тип — категория, материал и т.д.)
                    // =========================================================
                    elseif (!empty($filter['VALUES'])):
                    ?>

                    <div class="catalog-filter__checkboxes" role="group" aria-label="<?= $filterName ?>">
                        <?php foreach ($filter['VALUES'] as $valueId => $value):
                            if (!isset($value['VALUE'])) continue;
                            $valueName  = htmlspecialcharsbx((string)($value['DISPLAY_VALUE'] ?? $value['VALUE'] ?? ''));
                            $isChecked  = !empty($value['CHECKED']);
                            $isDisabled = !empty($value['DISABLED']);
                            $itemCount  = isset($value['ELEMENT_COUNT']) ? (int)$value['ELEMENT_COUNT'] : null;
                            $inputId    = 'filter-' . htmlspecialcharsbx($filterCode) . '-' . htmlspecialcharsbx((string)$valueId);
                            $inputName  = $fieldName . '[' . htmlspecialcharsbx((string)$valueId) . ']';
                        ?>

                        <label
                            class="catalog-filter__checkbox-item<?= $isChecked ? ' catalog-filter__checkbox-item--checked' : '' ?><?= $isDisabled ? ' catalog-filter__checkbox-item--disabled' : '' ?>"
                            for="<?= $inputId ?>"
                        >
                            <input
                                class="catalog-filter__checkbox-input"
                                type="checkbox"
                                id="<?= $inputId ?>"
                                name="<?= $inputName ?>"
                                value="<?= htmlspecialcharsbx((string)($value['VALUE'] ?? '')) ?>"
                                <?= $isChecked  ? 'checked'   : '' ?>
                                <?= $isDisabled ? 'disabled'  : '' ?>
                                data-filter-input
                                data-filter-code="<?= htmlspecialcharsbx($filterCode) ?>"
                            >
                            <!-- Кастомная галочка через CSS/SVG, без <i>/<span> с bg -->
                            <span class="catalog-filter__checkbox-mark" aria-hidden="true">
                                <svg class="catalog-filter__checkbox-check" width="10" height="8" viewBox="0 0 10 8" fill="none" aria-hidden="true">
                                    <path d="M1 4l3 3 5-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="catalog-filter__checkbox-label"><?= $valueName ?></span>
                            <?php if ($itemCount !== null): ?>
                            <span class="catalog-filter__checkbox-count" aria-label="товаров: <?= $itemCount ?>"><?= $itemCount ?></span>
                            <?php endif; ?>
                        </label>

                        <?php endforeach; ?>
                    </div>

                    <?php endif; ?>

                </div>
                <!-- /group-body -->

            </div>
            <!-- /group -->

            <?php endforeach; ?>

        </div>
        <!-- /groups -->

        <?php endif; // FILTERS не пустые ?>

        <!-- Кнопки действий -->
        <div class="catalog-filter__footer">
            <button
                class="btn btn--ghost catalog-filter__btn-reset"
                type="button"
                data-filter-reset
                aria-label="Сбросить все фильтры"
            >Сбросить</button>
            <button
                class="btn btn--primary catalog-filter__btn-apply"
                type="submit"
                data-filter-apply
                aria-label="Применить фильтры"
            >Применить</button>
        </div>

    </form>
    <!-- /form -->

</div>
<!-- /catalog-filter -->
