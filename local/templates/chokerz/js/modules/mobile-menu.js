/**
 * Модуль мобильного меню
 * 
 * @package CHOKERZ
 * @version 1.0.0
 */

class MobileMenu {
    constructor() {
        this.burgerBtn = null;
        this.nav = null;
        this.isOpen = false;
        this.initComplete = false;
    }

    /**
     * Инициализация модуля
     */
    init() {
        this.burgerBtn = document.getElementById('burgerBtn');
        this.nav = document.getElementById('mainNav');

        if (!this.burgerBtn || !this.nav) {
            console.warn('CHOKERZ: Мобильное меню не найдено');
            return this;
        }

        this.bindEvents();
        this.initComplete = true;

        console.log('CHOKERZ: Мобильное меню инициализировано');

        return this;
    }

    /**
     * Привязка событий
     */
    bindEvents() {
        // Клик по бургер-кнопке
        this.burgerBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggle();
        });

        // Клик вне меню для закрытия
        document.addEventListener('click', (e) => {
            if (this.isOpen && !this.nav.contains(e.target) && !this.burgerBtn.contains(e.target)) {
                this.close();
            }
        });

        // Закрытие меню при нажатии на ссылку
        const navLinks = this.nav.querySelectorAll('.nav__link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                this.close();
            });
        });

        // Закрытие меню при изменении размера окна (если экран стал большим)
        window.addEventListener('resize', () => {
            if (window.innerWidth > 992 && this.isOpen) {
                this.close();
            }
        });
    }

    /**
     * Переключение состояния меню
     */
    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    /**
     * Открытие меню
     */
    open() {
        this.nav.style.display = 'block';
        this.isOpen = true;
        this.burgerBtn.setAttribute('aria-expanded', 'true');
        
        // Анимация появления
        setTimeout(() => {
            this.nav.style.opacity = '1';
            this.nav.style.transform = 'translateY(0)';
        }, 10);

        // Блокировка прокрутки страницы
        document.body.style.overflow = 'hidden';

        console.log('CHOKERZ: Мобильное меню открыто');
    }

    /**
     * Закрытие меню
     */
    close() {
        this.nav.style.opacity = '0';
        this.nav.style.transform = 'translateY(-20px)';
        this.isOpen = false;
        this.burgerBtn.setAttribute('aria-expanded', 'false');
        
        // Скрытие меню после анимации
        setTimeout(() => {
            this.nav.style.display = 'none';
        }, 300);

        // Разблокировка прокрутки страницы
        document.body.style.overflow = '';

        console.log('CHOKERZ: Мобильное меню закрыто');
    }

    /**
     * Проверка инициализации
     */
    isInitialized() {
        return this.initComplete;
    }
}

// Создание экземпляра и экспорт
const mobileMenu = new MobileMenu();

export default mobileMenu;
