/**
 * Модуль поиска
 * 
 * @package CHOKERZ
 * @version 1.0.0
 */

class Search {
    constructor() {
        this.searchInput = null;
        this.searchForm = null;
        this.initComplete = false;
    }

    /**
     * Инициализация модуля
     */
    init() {
        this.searchInput = document.querySelector('.search__input');
        this.searchForm = document.querySelector('.search__form');

        if (!this.searchInput) {
            console.warn('CHOKERZ: Поле поиска не найдено');
            return this;
        }

        this.bindEvents();
        this.initComplete = true;

        console.log('CHOKERZ: Поиск инициализирован');

        return this;
    }

    /**
     * Привязка событий
     */
    bindEvents() {
        // Фокус на поле поиска
        this.searchInput.addEventListener('focus', () => {
            this.onFocus();
        });

        // Потеря фокуса
        this.searchInput.addEventListener('blur', () => {
            this.onBlur();
        });

        // Ввод текста (можно добавить автодополнение)
        this.searchInput.addEventListener('input', (e) => {
            this.onInput(e);
        });

        // Отправка формы
        if (this.searchForm) {
            this.searchForm.addEventListener('submit', (e) => {
                this.onSubmit(e);
            });
        }
    }

    /**
     * Обработчик фокуса
     */
    onFocus() {
        this.searchInput.parentElement?.classList.add('search__form--focused');
    }

    /**
     * Обработчик потери фокуса
     */
    onBlur() {
        this.searchInput.parentElement?.classList.remove('search__form--focused');
    }

    /**
     * Обработчик ввода текста
     * @param {Event} e - Событие ввода
     */
    onInput(e) {
        const value = e.target.value.trim();
        
        // Можно добавить автодополнение здесь
        if (value.length > 2) {
            this.showAutocomplete(value);
        } else {
            this.hideAutocomplete();
        }
    }

    /**
     * Обработчик отправки формы
     * @param {Event} e - Событие отправки
     */
    onSubmit(e) {
        const value = this.searchInput.value.trim();
        
        if (value.length === 0) {
            e.preventDefault();
            this.searchInput.focus();
            return false;
        }

        console.log('CHOKERZ: Поиск:', value);
        return true;
    }

    /**
     * Показать автодополнение
     * @param {string} query - Поисковый запрос
     */
    showAutocomplete(query) {
        // Здесь можно реализовать автодополнение через AJAX
        // Например, запрос к /ajax/search.php?q=query
        console.log('CHOKERZ: Автодополнение для:', query);
    }

    /**
     * Скрыть автодополнение
     */
    hideAutocomplete() {
        // Скрыть результаты автодополнения
    }

    /**
     * Проверка инициализации
     */
    isInitialized() {
        return this.initComplete;
    }
}

// Создание экземпляра и экспорт
const search = new Search();

export default search;
