/**
 * Точка входа для всех модулей сайта CHOKERZ
 * 
 * @package CHOKERZ
 * @version 1.0.0
 */

// ========================================
// Полифиллы для старых браузеров
// ========================================

// Полифилл для NodeList.forEach (IE11)
if (window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = Array.prototype.forEach;
}

// Полифилл для Element.closest (IE11)
if (!Element.prototype.closest) {
    Element.prototype.closest = function(s) {
        let el = this;
        do {
            if (el.matches(s)) return el;
            el = el.parentElement || el.parentNode;
        } while (el !== null && el.nodeType === 1);
        return null;
    };
}

// ========================================
// Импорт модулей
// ========================================

// Мобильное меню
import mobileMenu from './modules/mobile-menu.js';

// Поиск
import search from './modules/search.js';

// Корзина
import cart from './modules/cart.js';

// Избранное
import wishlist from './modules/wishlist.js';

// Модальные окна
import modals from './modules/modals.js';

// Формы и валидация
import forms from './modules/forms.js';

// Слайдеры и карусели
import sliders from './modules/sliders.js';

// Lazy load изображений
import lazyLoad from './modules/lazy-load.js';

// ========================================
// Инициализация приложения
// ========================================

class App {
    constructor() {
        this.modules = {};
        this.init();
    }

    /**
     * Инициализация всех модулей
     */
    init() {
        console.log('CHOKERZ: Инициализация приложения...');

        // Инициализация модулей
        this.initMobileMenu();
        this.initSearch();
        this.initCart();
        this.initWishlist();
        this.initModals();
        this.initForms();
        this.initSliders();
        this.initLazyLoad();

        // События
        this.bindEvents();

        console.log('CHOKERZ: Приложение инициализировано');
    }

    /**
     * Инициализация мобильного меню
     */
    initMobileMenu() {
        this.modules.mobileMenu = mobileMenu.init();
    }

    /**
     * Инициализация поиска
     */
    initSearch() {
        this.modules.search = search.init();
    }

    /**
     * Инициализация корзины
     */
    initCart() {
        this.modules.cart = cart.init();
    }

    /**
     * Инициализация избранного
     */
    initWishlist() {
        this.modules.wishlist = wishlist.init();
    }

    /**
     * Инициализация модальных окон
     */
    initModals() {
        this.modules.modals = modals.init();
    }

    /**
     * Инициализация форм
     */
    initForms() {
        this.modules.forms = forms.init();
    }

    /**
     * Инициализация слайдеров
     */
    initSliders() {
        this.modules.sliders = sliders.init();
    }

    /**
     * Инициализация lazy load
     */
    initLazyLoad() {
        this.modules.lazyLoad = lazyLoad.init();
    }

    /**
     * Привязка событий
     */
    bindEvents() {
        // Событие при полной загрузке страницы
        window.addEventListener('load', () => {
            this.onLoad();
        });

        // Событие при изменении размера окна
        window.addEventListener('resize', () => {
            this.onResize();
        });

        // Событие при прокрутке страницы
        window.addEventListener('scroll', () => {
            this.onScroll();
        });
    }

    /**
     * Обработчик полной загрузки страницы
     */
    onLoad() {
        console.log('CHOKERZ: Страница полностью загружена');
        
        // Убираем класс no-js
        document.documentElement.classList.remove('no-js');
        
        // Запуск анимаций
        this.runAnimations();
    }

    /**
     * Обработчик изменения размера окна
     */
    onResize() {
        // Можно добавить логику для адаптивности
    }

    /**
     * Обработчик прокрутки страницы
     */
    onScroll() {
        // Плавное появление элементов при прокрутке
        this.handleScrollAnimations();
    }

    /**
     * Запуск анимаций при загрузке
     */
    runAnimations() {
        // Анимация появления контента
        document.body.style.opacity = '0';
        document.body.style.transition = 'opacity 0.5s ease';
        
        setTimeout(() => {
            document.body.style.opacity = '1';
        }, 100);
    }

    /**
     * Анимации при прокрутке
     */
    handleScrollAnimations() {
        const scrollY = window.scrollY;
        
        // Плавное появление шапки при прокрутке
        const header = document.querySelector('.header');
        if (header) {
            if (scrollY > 100) {
                header.style.boxShadow = 'var(--shadow-md)';
            } else {
                header.style.boxShadow = 'var(--shadow-sm)';
            }
        }
    }

    /**
     * Получение модуля
     * @param {string} name - Имя модуля
     * @returns {*} Модуль или null
     */
    getModule(name) {
        return this.modules[name] || null;
    }

    /**
     * Добавление модуля
     * @param {string} name - Имя модуля
     * @param {*} module - Модуль
     */
    addModule(name, module) {
        this.modules[name] = module;
    }
}

// ========================================
// Запуск приложения
// ========================================

document.addEventListener('DOMContentLoaded', () => {
    window.chokerz = new App();
});

// Экспорт для использования в других модулях
export default App;
