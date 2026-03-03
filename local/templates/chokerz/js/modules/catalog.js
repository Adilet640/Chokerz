/**
 * catalog.js — модуль каталога CHOKERZ
 *
 * Реализует:
 *  - AJAX-пагинация «Загрузить ещё» (SEO: кнопка содержит <a href>, п.8.2 ТЗ)
 *  - Режим поиска по GET ?q= с вкладками Все / Товары / Информация
 *  - Переключение вида grid / list
 *  - Сортировка: select → autosubmit form
 *
 * sessionStorage используется исключительно для хранения UI-предпочтения
 * (вид сетки grid/list) — не является данными магазина, не подпадает под
 * запрет localStorage из п.6.1 ТЗ. localStorage для данных магазина — запрещён.
 *
 * @package CHOKERZ
 * @version 1.1
 */

const DEBUG = document.documentElement.dataset.debug !== undefined;

/** @param {...*} args */
function log(...args) {
    if (DEBUG) console.log('[CHOKERZ:catalog]', ...args);
}

// ─────────────────────────────────────────────────────────────
// Константы
// ─────────────────────────────────────────────────────────────
const SESSION_KEY_VIEW   = 'chokerz_catalog_view'; // UI-стейт, не данные магазина
const CSS_LIST_MOD       = 'catalog-grid--list';
const CSS_SEARCH_MODE    = 'catalog--search-mode';
const CSS_LOADING        = 'catalog-load-more--loading';
const CSS_HIDDEN         = 'is-hidden';
const CSS_ACTIVE         = 'is-active';
const ATTR_VIEW          = 'data-view';
const ATTR_CATALOG_GRID  = 'data-catalog-grid';
const ATTR_LOADMORE      = 'data-loadmore';
const ATTR_LOADMORE_WRAP = 'data-loadmore-wrap';
const ATTR_SEARCH_TABS   = 'data-search-tabs';
const ATTR_TAB           = 'data-tab';
const ATTR_ITEM_TYPE     = 'data-item-type';
const ATTR_SORT_SELECT   = 'data-sort-select';
const ATTR_SORT_FORM     = 'data-sort-form';

// ─────────────────────────────────────────────────────────────
// Разделённое состояние модуля (замыкание, не window.*)
// ─────────────────────────────────────────────────────────────

/**
 * Внутренний стейт каталога.
 * Передаётся между initLoadMore и initSearchMode через параметры init-функций.
 * Единственный источник правды об активной вкладке поиска.
 */
const state = {
    /** @type {'all'|'product'|'info'} */
    activeTab: 'all',

    /** @type {((articles: HTMLElement[]) => void)|null} Колбэк фильтрации для loadMore */
    applyTabToArticles: null,
};

// ─────────────────────────────────────────────────────────────
// Вспомогательные функции
// ─────────────────────────────────────────────────────────────

/**
 * Получить значение GET-параметра из текущего URL.
 * @param {string} name
 * @returns {string|null}
 */
function getQueryParam(name) {
    return new URLSearchParams(window.location.search).get(name);
}

/**
 * Парсит HTML-строку через DOMParser.
 * @param {string} html
 * @returns {Document}
 */
function parseHTML(html) {
    return new DOMParser().parseFromString(html, 'text/html');
}

/**
 * Применить видимость к набору article-элементов по текущей вкладке.
 * Используется как при переключении вкладок, так и после AJAX-подгрузки.
 *
 * @param {HTMLElement[]} articles
 * @param {'all'|'product'|'info'} tabValue
 */
function filterArticlesByTab(articles, tabValue) {
    articles.forEach(article => {
        const type    = article.getAttribute(ATTR_ITEM_TYPE);
        const visible = tabValue === 'all' || type === tabValue;
        article.classList.toggle(CSS_HIDDEN, !visible);
    });
}

// ─────────────────────────────────────────────────────────────
// 1. AJAX-пагинация «Загрузить ещё»
// ─────────────────────────────────────────────────────────────

/**
 * Ожидаемая HTML-структура:
 *
 * <div data-loadmore-wrap>
 *   <button data-loadmore type="button">
 *     <a href="/catalog/dogs/?page=2" data-loadmore-link>Загрузить ещё</a>
 *   </button>
 * </div>
 * <div data-catalog-grid class="catalog-grid">
 *   <article data-item-type="product"> ... </article>
 * </div>
 *
 * SEO: rel=next/prev пагинация остаётся в <head> на сервере (п.8.2 ТЗ).
 * Кнопка «Загрузить ещё» обязательно содержит <a href> внутри (п.8.2 ТЗ).
 */
function initLoadMore() {
    const wrap = document.querySelector(`[${ATTR_LOADMORE_WRAP}]`);
    const btn  = wrap ? wrap.querySelector(`[${ATTR_LOADMORE}]`) : null;
    const grid = document.querySelector(`[${ATTR_CATALOG_GRID}]`);

    if (!wrap || !btn || !grid) {
        log('loadMore: элементы не найдены, инициализация пропущена');
        return;
    }

    // Кешируем ссылку однократно; при обновлении href мутируем тот же элемент
    const link = btn.querySelector('a[href]');

    if (!link) {
        log('loadMore: <a href> внутри кнопки не найден');
        return;
    }

    btn.addEventListener('click', async (e) => {
        e.preventDefault();

        if (btn.classList.contains(CSS_LOADING)) return;

        const url = link.getAttribute('href');
        if (!url) return;

        btn.classList.add(CSS_LOADING);
        log('loadMore: fetch', url);

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const html    = await response.text();
            const doc     = parseHTML(html);
            const newGrid = doc.querySelector(`[${ATTR_CATALOG_GRID}]`);
            const newWrap = doc.querySelector(`[${ATTR_LOADMORE_WRAP}]`);
            const newLink = newWrap ? newWrap.querySelector('a[href]') : null;

            const articles = newGrid
                ? [...newGrid.querySelectorAll('article')]
                : [];

            if (articles.length === 0) {
                log('loadMore: карточки не найдены в ответе');
                wrap.classList.add(CSS_HIDDEN);
                return;
            }

            // ─── ИСПРАВЛЕНИЕ дефекта [строки 168–175 v1.0] ───────────────
            // Перед вставкой в DOM применяем фильтр активной вкладки поиска.
            // state.applyTabToArticles устанавливается в initSearchMode().
            // Если режим поиска не активен — колбэк null, фильтрация не нужна.
            if (state.applyTabToArticles !== null) {
                state.applyTabToArticles(articles);
                log('loadMore: фильтр вкладки применён к новым карточкам, вкладка =', state.activeTab);
            }
            // ─────────────────────────────────────────────────────────────

            const fragment = document.createDocumentFragment();
            articles.forEach(article => fragment.appendChild(article));
            grid.appendChild(fragment);

            log(`loadMore: добавлено ${articles.length} карточек`);

            // Обновляем href в кешированной SEO-ссылке
            if (newLink) {
                link.setAttribute('href', newLink.getAttribute('href'));
                log('loadMore: следующая страница →', newLink.getAttribute('href'));
            } else {
                wrap.classList.add(CSS_HIDDEN);
                log('loadMore: достигнута последняя страница');
            }

        } catch (err) {
            if (DEBUG) console.error('[CHOKERZ:catalog] loadMore error:', err);
        } finally {
            btn.classList.remove(CSS_LOADING);
        }
    });

    log('loadMore: инициализирован');
}

// ─────────────────────────────────────────────────────────────
// 2. Режим поиска
// ─────────────────────────────────────────────────────────────

/**
 * Ожидаемая HTML-структура:
 *
 * <div data-search-tabs class="is-hidden">
 *   <button data-tab="all"     class="is-active">Все</button>
 *   <button data-tab="product">Товары</button>
 *   <button data-tab="info">Информация</button>
 * </div>
 * <div data-catalog-grid>
 *   <article data-item-type="product"> ... </article>
 *   <article data-item-type="info">    ... </article>
 * </div>
 */
function initSearchMode() {
    const query = getQueryParam('q');
    if (!query) return;

    document.body.classList.add(CSS_SEARCH_MODE);
    log('searchMode: активирован, запрос =', query);

    const tabsWrap = document.querySelector(`[${ATTR_SEARCH_TABS}]`);
    const grid     = document.querySelector(`[${ATTR_CATALOG_GRID}]`);

    if (!tabsWrap || !grid) {
        log('searchMode: вкладки или грид не найдены');
        return;
    }

    tabsWrap.classList.remove(CSS_HIDDEN);

    const tabs = [...tabsWrap.querySelectorAll(`[${ATTR_TAB}]`)];

    /**
     * Применить фильтр вкладки ко всем article в гриде.
     * Обновляет state.activeTab — единственная точка мутации стейта.
     *
     * @param {'all'|'product'|'info'} tabValue
     */
    const applyTab = (tabValue) => {
        state.activeTab = tabValue;

        tabs.forEach(t =>
            t.classList.toggle(CSS_ACTIVE, t.dataset.tab === tabValue)
        );

        const allArticles = [...grid.querySelectorAll(`[${ATTR_ITEM_TYPE}]`)];
        filterArticlesByTab(allArticles, tabValue);

        log('searchMode: вкладка →', tabValue);
    };

    // ─── Регистрируем колбэк для initLoadMore ─────────────────────────
    // initLoadMore вызывает этот колбэк для новых карточек ДО вставки в DOM,
    // используя state.activeTab как источник правды.
    state.applyTabToArticles = (articles) => {
        filterArticlesByTab(articles, state.activeTab);
    };
    // ──────────────────────────────────────────────────────────────────

    tabsWrap.addEventListener('click', (e) => {
        const tab = e.target.closest(`[${ATTR_TAB}]`);
        if (!tab) return;
        applyTab(/** @type {'all'|'product'|'info'} */ (tab.dataset.tab));
    });

    // Активировать вкладку «Все» по умолчанию
    applyTab('all');

    log('searchMode: вкладки инициализированы');
}

// ─────────────────────────────────────────────────────────────
// 3. Переключение вида grid / list
// ─────────────────────────────────────────────────────────────

/**
 * Ожидаемая HTML-структура:
 *
 * <button data-view="grid" aria-pressed="true">Сетка</button>
 * <button data-view="list" aria-pressed="false">Список</button>
 * <div data-catalog-grid class="catalog-grid"> ... </div>
 *
 * sessionStorage: UI-предпочтение, не данные магазина (см. комментарий к константе).
 */
function initViewToggle() {
    const grid    = document.querySelector(`[${ATTR_CATALOG_GRID}]`);
    const buttons = [...document.querySelectorAll(`[${ATTR_VIEW}]`)];

    if (!grid || buttons.length === 0) {
        log('viewToggle: элементы не найдены');
        return;
    }

    /**
     * Применить вид, обновить aria-pressed и CSS-модификатор.
     * @param {'grid'|'list'} view
     */
    const applyView = (view) => {
        grid.classList.toggle(CSS_LIST_MOD, view === 'list');

        buttons.forEach(btn => {
            const isActive = btn.getAttribute(ATTR_VIEW) === view;
            btn.classList.toggle(CSS_ACTIVE, isActive);
            btn.setAttribute('aria-pressed', String(isActive));
        });

        log('viewToggle: вид →', view);
    };

    // Восстановить из sessionStorage
    const saved = sessionStorage.getItem(SESSION_KEY_VIEW);
    if (saved === 'list' || saved === 'grid') {
        applyView(saved);
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const view = btn.getAttribute(ATTR_VIEW);
            if (view !== 'grid' && view !== 'list') return;

            applyView(view);

            try {
                sessionStorage.setItem(SESSION_KEY_VIEW, view);
            } catch (err) {
                if (DEBUG) console.warn('[CHOKERZ:catalog] sessionStorage недоступен:', err);
            }
        });
    });

    log('viewToggle: инициализирован');
}

// ─────────────────────────────────────────────────────────────
// 4. Сортировка — autosubmit
// ─────────────────────────────────────────────────────────────

/**
 * Ожидаемая HTML-структура:
 *
 * <form data-sort-form method="get" action="">
 *   <select data-sort-select name="sort"> ... </select>
 * </form>
 */
function initSort() {
    const select = document.querySelector(`[${ATTR_SORT_SELECT}]`);
    if (!select) {
        log('sort: select не найден');
        return;
    }

    const form = select.closest(`[${ATTR_SORT_FORM}]`);
    if (!form) {
        log('sort: форма [data-sort-form] не найдена');
        return;
    }

    select.addEventListener('change', () => {
        log('sort: change →', select.value);
        form.submit();
    });

    log('sort: инициализирован');
}

// ─────────────────────────────────────────────────────────────
// Публичный API
// ─────────────────────────────────────────────────────────────

export default {
    init() {
        // Порядок важен: initSearchMode регистрирует state.applyTabToArticles
        // до initLoadMore, чтобы колбэк был доступен при первом AJAX-запросе.
        initSearchMode();
        initLoadMore();
        initViewToggle();
        initSort();
        log('catalog: модуль инициализирован');
    },
};
