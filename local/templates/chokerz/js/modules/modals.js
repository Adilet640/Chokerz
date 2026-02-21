/**
 * Модуль: Модальные окна
 * 
 * @package CHOKERZ
 * @version 1.0.0
 */

class Modals {
    constructor() {
        this.modals = [];
        this.activeModal = null;
    }

    /**
     * Инициализация модуля
     */
    init() {
        console.log('CHOKERZ: Инициализация модальных окон');
        
        this.initModals();
        this.bindEvents();
        
        return this;
    }

    /**
     * Инициализация всех модальных окон
     */
    initModals() {
        const modals = document.querySelectorAll('[data-modal]');
        
        modals.forEach(modal => {
            const id = modal.getAttribute('data-modal');
            const triggers = document.querySelectorAll(`[data-modal-trigger="${id}"]`);
            const closeBtns = modal.querySelectorAll('[data-modal-close]');
            
            this.modals.push({
                id,
                element: modal,
                triggers,
                closeBtns
            });
            
            // Скрыть модальное окно при инициализации
            modal.style.display = 'none';
        });
    }

    /**
     * Привязка событий
     */
    bindEvents() {
        // Открытие модального окна
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('[data-modal-trigger]');
            if (trigger) {
                e.preventDefault();
                const modalId = trigger.getAttribute('data-modal-trigger');
                this.open(modalId);
            }
        });

        // Закрытие модального окна
        document.addEventListener('click', (e) => {
            const closeBtn = e.target.closest('[data-modal-close]');
            if (closeBtn) {
                e.preventDefault();
                this.close();
            }
        });

        // Закрытие по клику на оверлей
        document.addEventListener('click', (e) => {
            if (this.activeModal && e.target === this.activeModal.element) {
                this.close();
            }
        });

        // Закрытие по нажатию клавиши Esc
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeModal) {
                this.close();
            }
        });
    }

    /**
     * Открытие модального окна
     * @param {string} id - ID модального окна
     */
    open(id) {
        const modal = this.modals.find(m => m.id === id);
        
        if (!modal) {
            console.warn(`CHOKERZ: Модальное окно с ID "${id}" не найдено`);
            return;
        }

        // Закрыть текущее активное модальное окно
        if (this.activeModal) {
            this.close();
        }

        // Показать модальное окно
        modal.element.style.display = 'block';
        this.activeModal = modal;

        // Добавить класс для блокировки прокрутки
        document.body.style.overflow = 'hidden';

        // Событие открытия
        const event = new CustomEvent('modal:opened', { detail: { id } });
        document.dispatchEvent(event);

        console.log(`CHOKERZ: Модальное окно "${id}" открыто`);
    }

    /**
     * Закрытие модального окна
     */
    close() {
        if (!this.activeModal) return;

        // Скрыть модальное окно
        this.activeModal.element.style.display = 'none';

        // Убрать блокировку прокрутки
        document.body.style.overflow = '';

        // Событие закрытия
        const event = new CustomEvent('modal:closed', { detail: { id: this.activeModal.id } });
        document.dispatchEvent(event);

        console.log(`CHOKERZ: Модальное окно "${this.activeModal.id}" закрыто`);

        // Очистить активное модальное окно
        this.activeModal = null;
    }

    /**
     * Получение активного модального окна
     * @returns {Object|null}
     */
    getActiveModal() {
        return this.activeModal;
    }
}

// Экспорт модуля
const modals = new Modals();
export default modals;
