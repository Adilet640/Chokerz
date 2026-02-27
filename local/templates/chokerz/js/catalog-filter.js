/**
 * /local/templates/chokerz/js/catalog-filter.js
 *
 * Управление панелью фильтров и сортировки каталога CHOKERZ.
 *
 * Функции:
 *   1. Аккордеон групп (filter + sort)
 *   2. Счётчик активных фильтров «Выбрано: N»
 *   3. Кнопка «Сбросить» — очистка всех чекбоксов/range + сабмит формы
 *   4. Мобильный drawer — клонирование sidebar-фильтра в drawer-контейнер
 *   5. Синхронизация sort hidden-полей при выборе radio
 *   6. Закрытие drawer по оверлею / Escape
 *
 * Требования ТЗ: Vanilla JS ES6+, без jQuery, без inline-скриптов, без глобальных переменных.
 * Все переменные — в IIFE / модульном scope.
 */

(() => {
    'use strict';

    // =========================================================================
    // Утилиты
    // =========================================================================

    /**
     * Найти ближайшего предка с data-атрибутом.
     * @param {Element} el
     * @param {string} attr
     * @returns {Element|null}
     */
    const closest = (el, attr) => {
        while (el) {
            if (el.dataset && attr in el.dataset) return el;
            el = el.parentElement;
        }
        return null;
    };

    // =========================================================================
    // 1. Аккордеон групп фильтра и сортировки
    // =========================================================================

    /**
     * Инициализация аккордеона по атрибуту data-accordion-toggle.
     * Работает для .catalog-filter__group и .catalog-sort__group.
     */
    const initAccordion = () => {
        document.addEventListener('click', (e) => {
            const toggle = e.target.closest('[data-accordion-toggle]');
            if (!toggle) return;

            const group  = toggle.closest('.catalog-filter__group, .catalog-sort__group');
            const bodyId = toggle.getAttribute('aria-controls');
            const body   = bodyId ? document.getElementById(bodyId) : null;

            if (!group || !body) return;

            const isExpanded = toggle.getAttribute('aria-expanded') === 'true';

            toggle.setAttribute('aria-expanded', String(!isExpanded));
            group.classList.toggle('catalog-filter__group--open', !isExpanded);
            group.classList.toggle('catalog-sort__group--open', !isExpanded);

            if (isExpanded) {
                body.setAttribute('hidden', '');
            } else {
                body.removeAttribute('hidden');
            }
        });
    };

    // =========================================================================
    // 2. Счётчик активных фильтров
    // =========================================================================

    /**
     * Подсчитывает активные фильтры (checked input[data-filter-input] или заполненные range).
     * Обновляет DOM-элемент счётчика.
     * @param {HTMLFormElement} form
     */
    const updateFilterCounter = (form) => {
        const counter = document.getElementById('filter-selected-count');
        const badge   = document.getElementById('filter-mobile-badge');
        if (!form) return;

        // Чекбоксы
        const checkedCount = form.querySelectorAll(
            'input[data-filter-input][type="checkbox"]:checked'
        ).length;

        // Range (заполнен хотя бы FROM или TO)
        const rangeCount = [...form.querySelectorAll('[data-filter-range]')].filter((wrap) => {
            const inputs = wrap.querySelectorAll('input[type="number"]');
            return [...inputs].some((inp) => inp.value.trim() !== '');
        }).length;

        const total = checkedCount + rangeCount;

        if (counter) {
            if (total > 0) {
                counter.textContent = `Выбрано: ${total}`;
                counter.removeAttribute('hidden');
                counter.classList.remove('catalog-filter__counter--empty');
            } else {
                counter.setAttribute('hidden', '');
            }
        }

        if (badge) {
            if (total > 0) {
                badge.textContent = String(total);
                badge.removeAttribute('hidden');
            } else {
                badge.setAttribute('hidden', '');
            }
        }
    };

    /**
     * Инициализация отслеживания изменений в форме фильтра.
     */
    const initFilterCounter = () => {
        const form = document.getElementById('smart-filter-form')
            ?? document.querySelector('[data-smart-filter-form]');
        if (!form) return;

        // Обновляем счётчик при любом изменении полей
        form.addEventListener('change', () => updateFilterCounter(form));

        // Первичный подсчёт по состоянию из Битрикс (arResult)
        updateFilterCounter(form);
    };

    // =========================================================================
    // 3. Кнопка «Сбросить»
    // =========================================================================

    const initFilterReset = () => {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-filter-reset]');
            if (!btn) return;

            const form = btn.closest('form') ?? document.querySelector('[data-smart-filter-form]');
            if (!form) return;

            // Сбрасываем чекбоксы
            form.querySelectorAll('input[type="checkbox"]').forEach((cb) => {
                cb.checked = false;
            });

            // Сбрасываем range-поля
            form.querySelectorAll('input[type="number"]').forEach((inp) => {
                inp.value = '';
            });

            // Снимаем CSS-модификаторы checked с label
            form.querySelectorAll('.catalog-filter__checkbox-item--checked').forEach((el) => {
                el.classList.remove('catalog-filter__checkbox-item--checked');
            });
            form.querySelectorAll('.catalog-filter__color-item--checked').forEach((el) => {
                el.classList.remove('catalog-filter__color-item--checked');
            });

            updateFilterCounter(form);

            // Сабмит — перейти на URL без параметров фильтра
            const filterUrl = form.closest('[data-filter-component]')?.dataset.filterUrl ?? '/catalog/';
            window.location.href = filterUrl;
        });
    };

    // =========================================================================
    // 4. Синхронизация CSS-состояния checkbox/radio при клике
    // =========================================================================

    const initFilterInputSync = () => {
        document.addEventListener('change', (e) => {
            const input = e.target;

            // Чекбокс фильтра
            if (input.matches('[data-filter-input][type="checkbox"]')) {
                const label = input.closest(
                    '.catalog-filter__checkbox-item, .catalog-filter__color-item'
                );
                if (label) {
                    label.classList.toggle('catalog-filter__checkbox-item--checked', input.checked);
                    label.classList.toggle('catalog-filter__color-item--checked', input.checked);
                }
            }

            // Radio сортировки — обновляем hidden-поля
            if (input.matches('[data-sort-input]')) {
                const sortBy    = input.dataset.sortBy    ?? '';
                const sortOrder = input.dataset.sortOrder ?? 'ASC';

                const hiddenBy    = document.getElementById('sort-hidden-by');
                const hiddenOrder = document.getElementById('sort-hidden-order');
                if (hiddenBy)    hiddenBy.value    = sortBy;
                if (hiddenOrder) hiddenOrder.value = sortOrder;

                // Снимаем --checked со всех, ставим на текущий
                input.closest('[data-sort-group]')
                    ?.querySelectorAll('.catalog-sort__option')
                    .forEach((opt) => opt.classList.remove('catalog-sort__option--checked'));

                input.closest('.catalog-sort__option')
                    ?.classList.add('catalog-sort__option--checked');
            }
        });
    };

    // =========================================================================
    // 5. Мобильный Drawer
    //    Клонируем sidebar-компоненты в drawer-контейнеры при первом открытии.
    //    Повторный клон не производим — используем cloned-флаг.
    // =========================================================================

    const initDrawers = () => {

        /**
         * Открыть drawer по его id.
         * @param {string} drawerId
         */
        const openDrawer = (drawerId) => {
            const drawer = document.getElementById(drawerId);
            if (!drawer) return;

            // Клонируем содержимое из sidebar при первом открытии
            if (!drawer.dataset.cloned) {
                if (drawerId === 'catalog-filter-popup') {
                    const src = document.getElementById('catalog-filter');
                    const dst = drawer.querySelector('[data-drawer-filter-content]');
                    if (src && dst) {
                        dst.appendChild(src.cloneNode(true));
                        drawer.dataset.cloned = '1';
                    }
                } else if (drawerId === 'catalog-sort-popup') {
                    const src = document.getElementById('catalog-sort');
                    const dst = drawer.querySelector('[data-drawer-sort-content]');
                    if (src && dst) {
                        dst.appendChild(src.cloneNode(true));
                        drawer.dataset.cloned = '1';
                    }
                }
            }

            drawer.removeAttribute('hidden');
            document.body.classList.add('body--drawer-open');

            // Обновляем aria-expanded на триггере
            document.querySelector(`[data-modal-open="${drawer.dataset.drawer}"]`)
                ?.setAttribute('aria-expanded', 'true');

            // Фокус на первый интерактивный элемент в панели
            const firstFocusable = drawer.querySelector('button, input, [tabindex="0"]');
            firstFocusable?.focus();
        };

        /**
         * Закрыть drawer.
         * @param {HTMLElement} drawer
         */
        const closeDrawer = (drawer) => {
            if (!drawer) return;
            drawer.setAttribute('hidden', '');
            document.body.classList.remove('body--drawer-open');

            document.querySelector(`[data-modal-open="${drawer.dataset.drawer}"]`)
                ?.setAttribute('aria-expanded', 'false');
        };

        // Открытие по кнопке
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('[data-modal-open]');
            if (!trigger) return;

            const targetId = trigger.dataset.modalOpen;
            if (!['filter-popup', 'sort-popup'].includes(targetId)) return;

            const drawerId = targetId === 'filter-popup' ? 'catalog-filter-popup' : 'catalog-sort-popup';
            openDrawer(drawerId);
        });

        // Закрытие по оверлею или data-drawer-close
        document.addEventListener('click', (e) => {
            const closeBtn = e.target.closest('[data-drawer-close]');
            if (!closeBtn) return;
            const drawer = closeBtn.closest('.catalog-drawer');
            closeDrawer(drawer);
        });

        // Закрытие по Escape
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            const openDrawer = document.querySelector('.catalog-drawer:not([hidden])');
            closeDrawer(openDrawer);
        });
    };

    // =========================================================================
    // Init
    // =========================================================================

    const init = () => {
        initAccordion();
        initFilterCounter();
        initFilterReset();
        initFilterInputSync();
        initDrawers();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
