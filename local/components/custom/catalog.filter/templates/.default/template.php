<?php
/**
 * Шаблон компонента фильтра каталога товаров (кастомный)
 * 
 * @author VibePilot
 * @version 1.0
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div class="catalog-filter" data-component="catalog.filter">
    <div class="catalog-filter__header">
        <h3 class="catalog-filter__title">Фильтр</h3>
        <button class="catalog-filter__toggle" data-action="toggle-filters">
            <span>Скрыть фильтр</span>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M6 18L18 6M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
    </div>

    <form class="catalog-filter__form" method="get" action="<?= $APPLICATION->GetCurPage() ?>">
        
        <!-- Сортировка -->
        <div class="catalog-filter__group">
            <h4 class="catalog-filter__group-title">Сортировка</h4>
            <select name="sort" class="catalog-filter__select" data-filter="sort">
                <?php foreach ($arResult['SORTING'] as $code => $name): ?>
                    <option value="<?= htmlspecialchars($code) ?>" <?= $arResult['CURRENT_SORT'] === $code ? 'selected' : '' ?>>
                        <?= htmlspecialchars($name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Фильтры по свойствам -->
        <?php foreach ($arResult['PROPERTIES'] as $propertyCode => $property): ?>
            <div class="catalog-filter__group" data-filter-group="<?= htmlspecialchars($propertyCode) ?>">
                <h4 class="catalog-filter__group-title">
                    <?= htmlspecialchars($property['NAME']) ?>
                </h4>

                <?php if ($propertyCode === 'COLOR'): ?>
                    <!-- Цвета (кружки) -->
                    <div class="catalog-filter__colors">
                        <?php foreach ($property['VALUES'] as $colorCode => $colorData): ?>
                            <label class="catalog-filter__color-item">
                                <input 
                                    type="checkbox" 
                                    name="filter[COLOR][]" 
                                    value="<?= htmlspecialchars($colorCode) ?>"
                                    data-filter="color"
                                >
                                <span 
                                    class="catalog-filter__color-preview" 
                                    style="background-color: <?= htmlspecialchars($colorData['HEX']) ?>"
                                    title="<?= htmlspecialchars($colorData['NAME']) ?>"
                                ></span>
                                <span class="catalog-filter__color-name"><?= htmlspecialchars($colorData['NAME']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Обычные чекбоксы -->
                    <div class="catalog-filter__options">
                        <?php foreach ($property['VALUES'] as $valueCode => $valueName): ?>
                            <label class="catalog-filter__option">
                                <input 
                                    type="checkbox" 
                                    name="filter[<?= htmlspecialchars($propertyCode) ?>][]" 
                                    value="<?= htmlspecialchars($valueCode) ?>"
                                    data-filter="<?= htmlspecialchars($propertyCode) ?>"
                                >
                                <span class="catalog-filter__option-text"><?= htmlspecialchars($valueName) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <!-- Кнопки действий -->
        <div class="catalog-filter__actions">
            <button type="submit" class="btn btn--primary btn--full">
                Применить фильтр
            </button>
            <button type="button" class="btn btn--link btn--full" data-action="reset-filters">
                Сбросить фильтр
            </button>
        </div>
    </form>
</div>

<style>
/* Стили фильтра */
.catalog-filter {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.catalog-filter__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.catalog-filter__title {
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
}

.catalog-filter__toggle {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #666;
}

.catalog-filter__group {
    margin-bottom: 20px;
}

.catalog-filter__group-title {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin: 0 0 10px 0;
    text-transform: uppercase;
}

.catalog-filter__select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    color: #333;
    background: #fff;
    cursor: pointer;
}

.catalog-filter__colors {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.catalog-filter__color-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
}

.catalog-filter__color-preview {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-bottom: 6px;
    border: 2px solid transparent;
    transition: border-color 0.2s;
}

.catalog-filter__color-item input:checked + .catalog-filter__color-preview {
    border-color: #000;
}

.catalog-filter__color-name {
    font-size: 12px;
    color: #666;
    text-align: center;
}

.catalog-filter__options {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.catalog-filter__option {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 8px 0;
}

.catalog-filter__option input[type="checkbox"] {
    margin: 0 8px 0 0;
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.catalog-filter__option-text {
    font-size: 14px;
    color: #333;
}

.catalog-filter__actions {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

@media (max-width: 768px) {
    .catalog-filter {
        padding: 15px;
    }

    .catalog-filter__title {
        font-size: 16px;
    }

    .catalog-filter__color-preview {
        width: 30px;
        height: 30px;
    }
}
</style>

<script>
// JavaScript для фильтра
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.querySelector('.catalog-filter__form');
    const resetButton = document.querySelector('[data-action="reset-filters"]');
    const toggleButton = document.querySelector('[data-action="toggle-filters"]');
    const filterContainer = document.querySelector('.catalog-filter');

    // Применение фильтра (отправка формы)
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Здесь можно добавить обработку через AJAX или просто отправить форму
            this.submit();
        });
    }

    // Сброс фильтра
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            if (filterForm) {
                filterForm.reset();
                // Можно добавить автоматическую отправку после сброса или обновление страницы
                // filterForm.submit();
            }
        });
    }

    // Переключение видимости фильтра (для мобильных)
    if (toggleButton) {
        toggleButton.addEventListener('click', function() {
            if (filterContainer) {
                filterContainer.classList.toggle('catalog-filter--hidden');
                const text = this.querySelector('span');
                if (text) {
                    text.textContent = filterContainer.classList.contains('catalog-filter--hidden') 
                        ? 'Показать фильтр' 
                        : 'Скрыть фильтр';
                }
            }
        });
    }
});
</script>
