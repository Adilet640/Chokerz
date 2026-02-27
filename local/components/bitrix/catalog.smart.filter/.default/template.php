
<?php
/**
 * template.php — bitrix:catalog.smart.filter / chokerz
 * Кастомный шаблон умного фильтра для каталога CHOKERZ.
 * Отличия от стандартного шаблона:
 *  - Цвет (COLOR) — кружки (color-swatch) вместо чекбоксов
 *  - Ценовой диапазон — слайдер (data-атрибуты для JS)
 *  - Группы фильтра сворачиваются на mobile (aria-expanded)
 *  - Кнопка «Применить» выполняет submit формы (стандартный Битрикс)
 *  - Inline-скрипты запрещены (ТЗ п.6.1) — логика в catalog.js
 *
 * Используемые переменные $arResult (стандартный smart.filter):
 *   ITEMS          — массив фильтров (тип, своиства, цена)
 *   FILTER_NAME    — имя переменной фильтра (arFilter_catalog)
 *   FORM_ACTION    — URL формы (текущая страница)


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

// Имя формы — используется Битрикс для JS-инициализации smart filter
$filterName = $arResult['FILTER_NAME'] ?? 'arFilter_catalog';
$formId     = 'catalog-filter-form';
?>

<!-- ════════════════════════════════════════════════════════════════════════════
     УМНЫЙ ФИЛЬТР
     ════════════════════════════════════════════════════════════════════════════ -->
<div class="catalog-filter" id="catalog-filter" data-catalog-filter>

    <!-- Заголовок фильтра (с кнопкой сброса) -->
    <div class="catalog-filter__header">
        <span class="catalog-filter__title">Фильтры</span>
        <a href="<?= htmlspecialcharsbx($arResult['FORM_ACTION'] ?? $APPLICATION->GetCurPage(false)) ?>"
           class="catalog-filter__reset"
           data-filter-reset
           aria-label="Сбросить все фильтры">
            Сбросить
        </a>
    </div>

    <!-- Форма фильтра — стандартная для smart.filter Битрикс -->
    <form id="<?= $formId ?>"
          class="catalog-filter__form"
          method="get"
          action="<?= htmlspecialcharsbx($arResult['FORM_ACTION'] ?? $APPLICATION->GetCurPage(false)) ?>"
          data-filter-form
          name="<?= $filterName ?>">

        <?php
        // Перебираем группы фильтра из $arResult['ITEMS']
        // Стандартная структура: ITEM_ID, ITEM_NAME, ITEM_TYPE, VALUES, SUB_MORE
        if (!empty($arResult['ITEMS'])):
        foreach ($arResult['ITEMS'] as $arFilterItem):
            $filterId   = htmlspecialcharsbx($arFilterItem['ITEM_ID']   ?? '');
            $filterName2 = htmlspecialchars($arFilterItem['ITEM_NAME']  ?? '', ENT_QUOTES, 'UTF-8');
            $filterType = $arFilterItem['ITEM_TYPE'] ?? 'L'; // L=список, N=число, S=строка
            $isColor    = strpos(strtoupper($filterId), 'COLOR') !== false;
            $isPrice    = $filterType === 'N' && (strpos(strtoupper($filterId), 'PRICE') !== false
                          || strpos(strtoupper($filterId), 'CATALOG_PRICE') !== false);
        ?>

        <!-- Группа фильтра: <?= $filterName2 ?> -->
        <div class="catalog-filter__group"
             data-filter-group="<?= $filterId ?>"
             data-type="<?= $isColor ? 'color' : ($isPrice ? 'price' : 'list') ?>">

            <!-- Заголовок группы — кликабельный для сворачивания (mobile) -->
            <button type="button"
                    class="catalog-filter__group-toggle"
                    aria-expanded="true"
                    aria-controls="filter-group-<?= $filterId ?>"
                    data-filter-group-toggle>
                <span class="catalog-filter__group-name"><?= $filterName2 ?></span>
                <!-- SVG: chevron -->
                <svg class="catalog-filter__group-chevron"
                     width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                     aria-hidden="true" focusable="false">
                    <polyline points="18 15 12 9 6 15"/>
                </svg>
            </button>

            <div class="catalog-filter__group-body"
                 id="filter-group-<?= $filterId ?>">

                <?php if ($isPrice): ?>
                <!-- ── Ценовой диапазон ─────────────────────────────────── -->
                <?php
                $minPrice  = (float)($arFilterItem['MIN'] ?? 0);
                $maxPrice  = (float)($arFilterItem['MAX'] ?? 99999);
                $fromField = $arFilterItem['FROM_FIELD_NAME'] ?? ('from_' . $filterId);
                $toField   = $arFilterItem['TO_FIELD_NAME']   ?? ('to_'   . $filterId);
                $curFrom   = (float)($arFilterItem['HTML_VALUE_FROM'] ?? $minPrice);
                $curTo     = (float)($arFilterItem['HTML_VALUE_TO']   ?? $maxPrice);
                ?>
                <div class="price-filter"
                     data-price-filter
                     data-min="<?= (int)$minPrice ?>"
                     data-max="<?= (int)$maxPrice ?>">

                    <div class="price-filter__inputs">
                        <label class="price-filter__input-wrap">
                            <span class="price-filter__input-label">от</span>
                            <input type="number"
                                   name="<?= htmlspecialcharsbx($fromField) ?>"
                                   id="price-from"
                                   class="price-filter__input price-filter__input--from"
                                   value="<?= (int)$curFrom ?>"
                                   min="<?= (int)$minPrice ?>"
                                   max="<?= (int)$maxPrice ?>"
                                   data-price-from
                                   aria-label="Цена от">
                            <span class="price-filter__currency">₽</span>
                        </label>
                        <span class="price-filter__sep" aria-hidden="true">—</span>
                        <label class="price-filter__input-wrap">
                            <span class="price-filter__input-label">до</span>
                            <input type="number"
                                   name="<?= htmlspecialcharsbx($toField) ?>"
                                   id="price-to"
                                   class="price-filter__input price-filter__input--to"
                                   value="<?= (int)$curTo ?>"
                                   min="<?= (int)$minPrice ?>"
                                   max="<?= (int)$maxPrice ?>"
                                   data-price-to
                                   aria-label="Цена до">
                            <span class="price-filter__currency">₽</span>
                        </label>
                    </div>

                    <!-- Двойной слайдер (управляется catalog.js) -->
                    <div class="price-filter__slider"
                         data-price-slider
                         aria-hidden="true">
                        <div class="price-filter__track">
                            <div class="price-filter__range" data-price-range></div>
                        </div>
                        <div class="price-filter__handle price-filter__handle--min"
                             data-price-handle="min"
                             role="slider"
                             aria-label="Минимальная цена"
                             aria-valuemin="<?= (int)$minPrice ?>"
                             aria-valuemax="<?= (int)$maxPrice ?>"
                             aria-valuenow="<?= (int)$curFrom ?>"
                             tabindex="0"></div>
                        <div class="price-filter__handle price-filter__handle--max"
                             data-price-handle="max"
                             role="slider"
                             aria-label="Максимальная цена"
                             aria-valuemin="<?= (int)$minPrice ?>"
                             aria-valuemax="<?= (int)$maxPrice ?>"
                             aria-valuenow="<?= (int)$curTo ?>"
                             tabindex="0"></div>
                    </div>

                </div>

                <?php elseif ($isColor): ?>
                <!-- ── Цвет — кружки (ТЗ п.7.1) ───────────────────────── -->
                <div class="catalog-filter__colors" role="group"
                     aria-label="Выбор цвета">
                    <?php
                    if (!empty($arFilterItem['VALUES'])):
                    foreach ($arFilterItem['VALUES'] as $arVal):
                        if (empty($arVal)) continue;
                        $valId    = htmlspecialcharsbx($arVal['VALUE_ID']   ?? '');
                        $valName  = htmlspecialchars($arVal['VALUE'] ?? '',  ENT_QUOTES, 'UTF-8');
                        $valField = htmlspecialcharsbx($arVal['FIELD_NAME'] ?? $filterId);
                        $valHex   = trim($arVal['XML_ID'] ?? '');
                        if ($valHex !== '' && $valHex[0] !== '#') {
                            $valHex = '#' . $valHex;
                        }
                        if (!preg_match('/^#[0-9A-Fa-f]{3,8}$/', $valHex)) {
                            $valHex = '#CCCCCC';
                        }
                        $isChecked = !empty($arVal['CHECKED']) && $arVal['CHECKED'] === 'Y';
                        $inputId  = 'filter-color-' . $valId;
                    ?>
                    <label class="color-swatch<?= $isChecked ? ' color-swatch--active' : '' ?>"
                           for="<?= $inputId ?>"
                           title="<?= $valName ?>">
                        <input type="checkbox"
                               id="<?= $inputId ?>"
                               class="color-swatch__input visually-hidden"
                               name="<?= $valField ?>[]"
                               value="<?= $valId ?>"
                               <?= $isChecked ? 'checked' : '' ?>
                               data-filter-value
                               aria-label="<?= $valName ?>">
                        <span class="color-swatch__dot"
                              style="background-color:<?= $valHex ?>"></span>
                        <span class="color-swatch__label"><?= $valName ?></span>
                    </label>
                    <?php endforeach; endif; ?>
                </div>

                <?php else: ?>
                <!-- ── Обычный список: чекбоксы ────────────────────────── -->
                <ul class="catalog-filter__list" role="list">
                    <?php
                    if (!empty($arFilterItem['VALUES'])):
                    foreach ($arFilterItem['VALUES'] as $arVal):
                        if (empty($arVal)) continue;
                        $valId    = htmlspecialcharsbx($arVal['VALUE_ID']   ?? '');
                        $valName  = htmlspecialchars($arVal['VALUE'] ?? '',  ENT_QUOTES, 'UTF-8');
                        $valField = htmlspecialcharsbx($arVal['FIELD_NAME'] ?? $filterId);
                        $valCount = (int)($arVal['ELEMENT_COUNT'] ?? 0);
                        $isChecked = !empty($arVal['CHECKED']) && $arVal['CHECKED'] === 'Y';
                        $isDisabled = $valCount === 0 && !$isChecked;
                        $inputId  = 'filter-' . $filterId . '-' . $valId;
                    ?>
                    <li class="catalog-filter__list-item<?= $isDisabled ? ' catalog-filter__list-item--disabled' : '' ?>"
                        role="listitem">
                        <label class="catalog-filter__checkbox-label"
                               for="<?= $inputId ?>">
                            <input type="checkbox"
                                   id="<?= $inputId ?>"
                                   class="catalog-filter__checkbox"
                                   name="<?= $valField ?>[]"
                                   value="<?= $valId ?>"
                                   <?= $isChecked  ? 'checked'  : '' ?>
                                   <?= $isDisabled ? 'disabled' : '' ?>
                                   data-filter-value
                                   aria-label="<?= $valName ?><?= $valCount > 0 ? ' (' . $valCount . ')' : '' ?>">
                            <span class="catalog-filter__checkbox-custom" aria-hidden="true"></span>
                            <span class="catalog-filter__checkbox-text"><?= $valName ?></span>
                            <?php if ($valCount > 0): ?>
                            <span class="catalog-filter__checkbox-count"
                                  aria-hidden="true"><?= $valCount ?></span>
                            <?php endif; ?>
                        </label>
                    </li>
                    <?php endforeach; endif; ?>
                </ul>
                <?php endif; ?>

            </div>
            <!-- /catalog-filter__group-body -->

        </div>
        <!-- /catalog-filter__group -->

        <?php endforeach; endif; ?>

        <!-- Кнопки действий -->
        <div class="catalog-filter__actions">
            <button type="submit"
                    class="btn btn--primary btn--full"
                    data-filter-submit>
                Показать товары
                <span class="catalog-filter__submit-count" data-filter-count></span>
            </button>
        </div>

    </form>

</div>
<!-- /catalog-filter -->
