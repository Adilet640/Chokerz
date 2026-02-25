/**
 * mobile-menu.js — модуль мобильного меню CHOKERZ
 *
 * Работает с HTML-структурой из header.php:
 *   #site-burger   — кнопка-бургер
 *   #site-nav      — навигационный блок
 *   #nav-overlay   — полупрозрачный оверлей
 *
 * Состояние передаётся ТОЛЬКО через CSS-классы и aria-атрибуты.
 * Никаких inline-стилей (element.style.*) не используется.
 *
 * @package   CHOKERZ
 * @version   2.0
 */

class MobileMenu {
    #burger  = null;
    #nav     = null;
    #overlay = null;
    #isOpen  = false;

    /** Инициализация. Возвращает this для цепочки вызовов. */
    init() {
        this.#burger  = document.getElementById('site-burger');
        this.#nav     = document.getElementById('site-nav');
        this.#overlay = document.getElementById('nav-overlay');

        if (!this.#burger || !this.#nav) {
            return this;
        }

        this.#bindEvents();
        return this;
    }

    /** Привязка всех слушателей */
    #bindEvents() {
        // Клик по бургеру
        this.#burger.addEventListener('click', () => this.toggle());

        // Клик по оверлею → закрыть
        this.#overlay?.addEventListener('click', () => this.close());

        // Ссылки внутри nav → закрыть (UX для SPA-переходов или якорей)
        this.#nav.querySelectorAll('.site-nav__link').forEach(link => {
            link.addEventListener('click', () => this.close());
        });

        // Escape → закрыть
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && this.#isOpen) {
                this.close();
                this.#burger.focus(); // вернуть фокус на бургер (a11y)
            }
        });

        // При переходе на desktop-ширину — принудительно закрыть
        const mq = window.matchMedia('(min-width: 992px)');
        mq.addEventListener('change', e => {
            if (e.matches && this.#isOpen) this.close();
        });
    }

    /** Переключить состояние меню */
    toggle() {
        this.#isOpen ? this.close() : this.open();
    }

    /** Открыть меню */
    open() {
        this.#isOpen = true;

        this.#nav.classList.add('site-nav--open');
        this.#overlay?.classList.add('nav-overlay--visible');
        this.#burger.classList.add('burger--active');

        this.#burger.setAttribute('aria-expanded', 'true');
        this.#burger.setAttribute('aria-label',    'Закрыть меню');
        this.#nav.setAttribute('aria-hidden',      'false');
        document.body.classList.add('body--nav-open');

        // Перевести фокус в первый пункт меню (a11y)
        this.#nav.querySelector('.site-nav__link')?.focus();
    }

    /** Закрыть меню */
    close() {
        this.#isOpen = false;

        this.#nav.classList.remove('site-nav--open');
        this.#overlay?.classList.remove('nav-overlay--visible');
        this.#burger.classList.remove('burger--active');

        this.#burger.setAttribute('aria-expanded', 'false');
        this.#burger.setAttribute('aria-label',    'Открыть меню');
        this.#nav.setAttribute('aria-hidden',      'true');
        document.body.classList.remove('body--nav-open');
    }
}

const mobileMenu = new MobileMenu();
export default mobileMenu;
