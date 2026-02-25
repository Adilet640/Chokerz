/**
 * search.js — модуль поискового модального окна CHOKERZ
 *
 * Работает с HTML-структурой из header.php:
 *   [data-action="search-open"]  — кнопка открытия (в .header-actions)
 *   [data-action="search-close"] — кнопка закрытия и оверлей
 *   #search-modal                — диалоговое окно (role="dialog")
 *   .search-modal__input         — поле ввода
 *   .search-modal__form          — форма (action="/search/", method="get")
 *
 * Состояние — только через CSS-классы и aria-атрибуты.
 * Никаких inline-стилей.
 *
 * @package   CHOKERZ
 * @version   2.0
 */

class Search {
    #modal       = null;
    #input       = null;
    #openBtns    = [];
    #closeBtns   = [];
    #triggerEl   = null; // элемент, с которого открыли — для возврата фокуса

    /** Инициализация */
    init() {
        this.#modal     = document.getElementById('search-modal');
        this.#input     = this.#modal?.querySelector('.search-modal__input');
        this.#openBtns  = [...document.querySelectorAll('[data-action="search-open"]')];
        this.#closeBtns = [...document.querySelectorAll('[data-action="search-close"]')];

        if (!this.#modal) {
            return this;
        }

        this.#bindEvents();
        return this;
    }

    #bindEvents() {
        // Открытие
        this.#openBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                this.#triggerEl = btn;
                this.open();
            });
        });

        // Закрытие через кнопки и оверлей (backdrop)
        this.#closeBtns.forEach(btn => {
            btn.addEventListener('click', () => this.close());
        });

        // Escape → закрыть
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && this.#isOpen()) {
                this.close();
            }
        });

        // Не передаём пустой запрос
        this.#modal.querySelector('.search-modal__form')
            ?.addEventListener('submit', e => this.#onSubmit(e));
    }

    /** Открыть модальное окно поиска */
    open() {
        this.#modal.classList.add('search-modal--open');
        this.#modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('body--search-open');

        // Обновить кнопки-триггеры
        this.#openBtns.forEach(btn => btn.setAttribute('aria-expanded', 'true'));

        // Автофокус на инпут (с задержкой — дать CSS-анимации начаться)
        requestAnimationFrame(() => {
            this.#input?.focus();
        });
    }

    /** Закрыть модальное окно поиска */
    close() {
        this.#modal.classList.remove('search-modal--open');
        this.#modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('body--search-open');

        this.#openBtns.forEach(btn => btn.setAttribute('aria-expanded', 'false'));

        // Вернуть фокус на элемент, с которого открыли (a11y)
        this.#triggerEl?.focus();
        this.#triggerEl = null;
    }

    /** Проверка — открыт ли модал */
    #isOpen() {
        return this.#modal.classList.contains('search-modal--open');
    }

    /** Валидация перед отправкой */
    #onSubmit(e) {
        const query = this.#input?.value.trim() ?? '';
        if (query.length === 0) {
            e.preventDefault();
            this.#input?.focus();
        }
    }
}

const search = new Search();
export default search;
