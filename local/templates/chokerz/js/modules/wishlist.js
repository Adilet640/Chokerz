/**
 * Wishlist — модуль избранного
 *
 * @project CHOKERZ
 * @version 2.1
 *
 * Разметка корневого элемента (передаёт конфиг без window.*):
 *   <div data-wishlist-root
 *        data-ajax-url="/local/ajax/wishlist.php"
 *        data-debug="false">…</div>
 *
 * Кнопка-триггер:
 *   <button
 *     type="button"
 *     data-action="wishlist"
 *     data-product-id="123"
 *     aria-pressed="false"
 *     title="Добавить в избранное">…</button>
 *
 * Счётчик в шапке:
 *   <span data-wishlist-count hidden>0</span>
 *
 * sessid передаётся через скрытый input (добавить в шаблон header.php):
 *   <input type="hidden" name="sessid" value="<?= bitrix_sessid() ?>">
 */

'use strict';

class Wishlist {
    /**
     * @param {object}  opts
     * @param {string}  opts.ajaxUrl  — путь к обработчику (из data-атрибута)
     * @param {boolean} opts.debug    — включить console.*
     */
    constructor({ ajaxUrl, debug = false } = {}) {
        this._ajaxUrl = ajaxUrl;
        this._debug   = debug;
        this._ids     = new Set();
        this._countEl = null;
    }

    // ── Публичное API ─────────────────────────────────────────────────────────

    async init() {
        this._countEl = document.querySelector('[data-wishlist-count]');

        document.addEventListener('click', this._handleClick.bind(this));

        try {
            const data = await this._post({ action: 'list' });
            if (data.success) {
                this._syncState(data.product_ids ?? [], data.count ?? 0);
            }
        } catch (e) {
            this._log('warn', 'не удалось загрузить список', e);
        }

        return this;
    }

    // ── Обработчик клика (делегированный) ────────────────────────────────────

    async _handleClick(e) {
        const btn = e.target.closest('[data-action="wishlist"]');
        if (!btn) return;

        e.preventDefault();

        const productId = btn.dataset.productId;
        if (!productId) return;

        // Оптимистичное обновление
        const wasActive = this._ids.has(productId);
        this._setButtonActive(btn, !wasActive);
        wasActive ? this._ids.delete(productId) : this._ids.add(productId);
        this._renderCount(this._ids.size);

        try {
            const data = await this._post({ action: 'toggle', productId });

            if (!data.success) {
                // Откат
                this._setButtonActive(btn, wasActive);
                wasActive ? this._ids.add(productId) : this._ids.delete(productId);
                this._renderCount(this._ids.size);
                this._log('error', 'ошибка сервера:', data.message);
                return;
            }

            this._syncState(data.product_ids ?? [], data.count ?? 0);

        } catch (err) {
            // Сетевой откат
            this._setButtonActive(btn, wasActive);
            wasActive ? this._ids.add(productId) : this._ids.delete(productId);
            this._renderCount(this._ids.size);
            this._log('error', 'сетевая ошибка', err);
        }
    }

    // ── Вспомогательные методы ────────────────────────────────────────────────

    async _post(params) {
        const sessid = document.querySelector('input[name="sessid"]')?.value ?? '';
        const body   = new URLSearchParams({ sessid, ...params });

        const res = await fetch(this._ajaxUrl, {
            method:      'POST',
            credentials: 'same-origin',
            headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
            body,
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
    }

    _syncState(ids, count) {
        this._ids = new Set(ids.map(String));
        this._renderCount(count);
        this._refreshButtons();
    }

    _renderCount(count) {
        if (!this._countEl) return;
        this._countEl.textContent = count;
        this._countEl.setAttribute('aria-label', `Товаров в избранном: ${count}`);
        count > 0
            ? this._countEl.removeAttribute('hidden')
            : this._countEl.setAttribute('hidden', '');
    }

    _setButtonActive(btn, isActive) {
        btn.setAttribute('aria-pressed', String(isActive));
        btn.classList.toggle('wishlist-btn--active', isActive);
        btn.title = isActive ? 'Удалить из избранного' : 'Добавить в избранное';
    }

    _refreshButtons() {
        document.querySelectorAll('[data-action="wishlist"][data-product-id]').forEach(btn => {
            this._setButtonActive(btn, this._ids.has(String(btn.dataset.productId)));
        });
    }

    /** Логирование — только при debug=true */
    _log(level, ...args) {
        if (this._debug) {
            console[level]('CHOKERZ Wishlist:', ...args);
        }
    }
}

// ── Авто-инициализация — только если есть элементы на странице ────────────────
function bootWishlist() {
    const root       = document.querySelector('[data-wishlist-root]');
    const hasButtons = document.querySelector('[data-action="wishlist"]');
    const hasCount   = document.querySelector('[data-wishlist-count]');

    if (!root && !hasButtons && !hasCount) return null;

    const ajaxUrl = root?.dataset.ajaxUrl ?? '/local/ajax/wishlist.php';
    const debug   = root?.dataset.debug === 'true';

    const instance = new Wishlist({ ajaxUrl, debug });
    instance.init();
    return instance;
}

const wishlist = document.readyState === 'loading'
    ? (() => { document.addEventListener('DOMContentLoaded', bootWishlist); return null; })()
    : bootWishlist();

export default wishlist;
