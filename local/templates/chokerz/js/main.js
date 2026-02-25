/**
 * main.js — точка входа JS-приложения CHOKERZ
 *
 * Подключается в footer.php как <script type="module">.
 * type="module" даёт: автоматический defer + строгий режим ('use strict').
 *
 * Запрещённые практики (исправлены по сравнению с v1.0):
 *  - [x] Убрана манипуляция document.body.style.opacity (inline стили)
 *  - [x] Убрана манипуляция header.style.boxShadow (inline стили)
 *  - [x] Убран window.chokerz (глобальная переменная)
 *  - [x] console.log только в режиме отладки
 *
 * @package   CHOKERZ
 * @version   2.0
 */

import mobileMenu from './modules/mobile-menu.js';
import search     from './modules/search.js';
import cart       from './modules/cart.js';
import wishlist   from './modules/wishlist.js';
import modals     from './modules/modals.js';
import forms      from './modules/forms.js';
import sliders    from './modules/sliders.js';
import lazyLoad   from './modules/lazy-load.js';

// ─────────────────────────────────────────────────────────────
// Режим отладки — включается наличием data-debug на <html>
// ─────────────────────────────────────────────────────────────
const DEBUG = document.documentElement.dataset.debug !== undefined;

function log(...args) {
    if (DEBUG) console.log('[CHOKERZ]', ...args);
}

// ─────────────────────────────────────────────────────────────
// Инициализация
// (DOMContentLoaded уже случился, т.к. type="module" → defer)
// ─────────────────────────────────────────────────────────────
function init() {
    log('Инициализация приложения');

    // Убираем класс no-js — CSS может использовать .no-js для fallback
    document.documentElement.classList.remove('no-js');
    document.documentElement.classList.add('js');

    // Инициализируем все модули
    mobileMenu.init();
    search.init();
    cart.init();
    wishlist.init();
    modals.init();
    forms.init();
    sliders.init();
    lazyLoad.init();

    // Подсвечиваем шапку при скролле через CSS-класс (не inline style!)
    initHeaderScroll();

    log('Приложение инициализировано');
}

/**
 * Добавляет/снимает модификатор .site-header--scrolled при прокрутке.
 * Анимация — в CSS, здесь только переключение класса.
 */
function initHeaderScroll() {
    const header = document.getElementById('site-header');
    if (!header) return;

    const SCROLL_THRESHOLD = 50;

    const update = () => {
        header.classList.toggle('site-header--scrolled', window.scrollY > SCROLL_THRESHOLD);
    };

    // Passive listener — не блокирует рендер
    window.addEventListener('scroll', update, { passive: true });

    // Проверить начальное состояние
    update();
}

// Запуск
init();
