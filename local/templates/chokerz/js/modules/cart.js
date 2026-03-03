/**
 * Модуль корзины — CHOKERZ
 * Состояние только с сервера. Никакого localStorage.
 *
 * @package CHOKERZ
 * @version 2.0.0
 */

const DEBUG = document.documentElement.dataset.debug !== undefined;

const log  = (...args) => { if (DEBUG) console.log('[CHOKERZ:Cart]', ...args); };
const warn = (...args) => { if (DEBUG) console.warn('[CHOKERZ:Cart]', ...args); };

// ── Утилиты ──────────────────────────────────────────────────────────────────

/**
 * Получить значение BX.message('sessid') или мета-тег.
 * Bitrix выводит sessid в глобальный объект BX через <script> в head.
 * @returns {string}
 */
function getSessid() {
    if (typeof BX !== 'undefined' && typeof BX.message === 'function') {
        return BX.message('sessid') || '';
    }
    // Запасной вариант — скрытое поле на странице
    const field = document.querySelector('input[name="sessid"]');
    return field ? field.value : '';
}

/**
 * POST к /local/ajax/cart.php
 * @param {Object} params
 * @returns {Promise<Object>}
 */
async function apiCall(params) {
    const body = new URLSearchParams({ sessid: getSessid(), ...params });

    const response = await fetch('/local/ajax/cart.php', {
        method:  'POST',
        headers: {
            'Content-Type':     'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body:    body.toString(),
    });

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    const data = await response.json();
    log('apiCall ←', params.action, data);
    return data;
}

// ── Обновление UI ─────────────────────────────────────────────────────────────

/**
 * Применить данные корзины к DOM
 * @param {{ count: number, total: number, items: Array }} payload
 */
function applyCartState({ count = 0 }) {
    const badges = document.querySelectorAll('[data-cart-count]');

    badges.forEach((el) => {
        el.textContent = count;

        if (count > 0) {
            el.removeAttribute('hidden');
            el.setAttribute('aria-label', `В корзине ${count} ${pluralItems(count)}`);
        } else {
            el.setAttribute('hidden', '');
            el.setAttribute('aria-label', 'Корзина пуста');
        }
    });
}

function pluralItems(n) {
    const mod10  = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return 'товар';
    if ([2, 3, 4].includes(mod10) && ![12, 13, 14].includes(mod100)) return 'товара';
    return 'товаров';
}

// Таймер скрытия тоста хранится в замыкании модуля
let _toastTimer = null;

/**
 * Показать всплывающее уведомление.
 * Стили управляются CSS-классами: .cart-toast, .cart-toast--success,
 * .cart-toast--error, .cart-toast--visible
 * @param {string} message
 * @param {'success'|'error'} type
 */
function showToast(message, type = 'success') {
    let toast = document.getElementById('chokerz-cart-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'chokerz-cart-toast';
        toast.className = 'cart-toast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        document.body.appendChild(toast);
    }

    toast.textContent = message;

    // Сброс модификаторов типа
    toast.classList.remove('cart-toast--success', 'cart-toast--error');
    toast.classList.add(`cart-toast--${type}`);

    // Показать
    toast.classList.add('cart-toast--visible');

    clearTimeout(_toastTimer);
    _toastTimer = setTimeout(() => {
        toast.classList.remove('cart-toast--visible');
    }, 3200);
}

// ── Обработчики действий ─────────────────────────────────────────────────────

async function handleAdd(btn) {
    const productId = btn.dataset.productId;
    if (!productId) { warn('add: нет data-product-id'); return; }

    btn.disabled = true;
    try {
        const data = await apiCall({ action: 'add', productId, quantity: btn.dataset.quantity || 1 });
        if (data.success) {
            applyCartState(data);
            showToast(data.message || 'Товар добавлен в корзину', 'success');
        } else {
            showToast(data.message || 'Ошибка добавления', 'error');
        }
    } catch (err) {
        warn('handleAdd error', err);
        showToast('Ошибка соединения с сервером', 'error');
    } finally {
        btn.disabled = false;
    }
}

async function handleRemove(btn) {
    const basketId = btn.dataset.basketId;
    if (!basketId) { warn('remove: нет data-basket-id'); return; }

    btn.disabled = true;
    try {
        const data = await apiCall({ action: 'remove', basketId });
        if (data.success) {
            applyCartState(data);
            showToast(data.message || 'Товар удалён', 'success');

            // Удалить строку товара из DOM, если она размечена
            const row = btn.closest('[data-basket-item]');
            if (row) row.remove();
        } else {
            showToast(data.message || 'Ошибка удаления', 'error');
        }
    } catch (err) {
        warn('handleRemove error', err);
        showToast('Ошибка соединения с сервером', 'error');
    } finally {
        btn.disabled = false;
    }
}

// ── Инициализация ─────────────────────────────────────────────────────────────

const cart = {
    /**
     * Инициализация модуля.
     * Вызывать один раз после DOMContentLoaded.
     */
    async init() {
        log('init');

        // Делегированный клик
        document.addEventListener('click', (e) => {
            const addBtn    = e.target.closest('[data-action="add-to-cart"]');
            const removeBtn = e.target.closest('[data-action="remove-from-cart"]');

            if (addBtn)    { e.preventDefault(); handleAdd(addBtn); }
            if (removeBtn) { e.preventDefault(); handleRemove(removeBtn); }
        });

        // Начальное состояние счётчика берём из DOM (PHP рендерит data-cart-count при загрузке страницы).
        // Дополнительный HTTP-запрос при init не нужен.
        const initialBadge = document.querySelector('[data-cart-count]');
        if (initialBadge) {
            const initialCount = parseInt(initialBadge.textContent, 10) || 0;
            applyCartState({ count: initialCount });
            log('initial count from DOM:', initialCount);
        }
    },
};

export default cart;
