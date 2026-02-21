/**
 * Модуль: Формы и валидация
 * 
 * @package CHOKERZ
 * @version 1.0.0
 */

class Forms {
    constructor() {
        this.forms = [];
    }

    /**
     * Инициализация модуля
     */
    init() {
        console.log('CHOKERZ: Инициализация форм');
        
        this.initForms();
        this.bindEvents();
        
        return this;
    }

    /**
     * Инициализация всех форм
     */
    initForms() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            this.forms.push({
                element: form,
                fields: form.querySelectorAll('input, select, textarea')
            });
            
            // Добавить класс для стилизации
            form.classList.add('form-initialized');
        });
    }

    /**
     * Привязка событий
     */
    bindEvents() {
        // Валидация при вводе
        document.addEventListener('input', (e) => {
            const field = e.target;
            if (field.closest('form')) {
                this.validateField(field);
            }
        });

        // Валидация при потере фокуса
        document.addEventListener('blur', (e) => {
            const field = e.target;
            if (field.closest('form')) {
                this.validateField(field);
            }
        }, true);

        // Отправка формы
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.tagName === 'FORM') {
                this.handleSubmit(form, e);
            }
        });
    }

    /**
     * Валидация поля
     * @param {HTMLElement} field - Поле формы
     */
    validateField(field) {
        let isValid = true;
        let errorMessage = '';

        // Получить правила валидации из data-атрибутов
        const required = field.hasAttribute('data-required');
        const pattern = field.getAttribute('data-pattern');
        const minLength = field.getAttribute('data-min-length');
        const maxLength = field.getAttribute('data-max-length');

        // Проверка на обязательное поле
        if (required && !field.value.trim()) {
            isValid = false;
            errorMessage = 'Это поле обязательно для заполнения';
        }

        // Проверка по паттерну
        if (isValid && pattern && field.value.trim()) {
            const regex = new RegExp(pattern);
            if (!regex.test(field.value)) {
                isValid = false;
                errorMessage = field.getAttribute('data-error-message') || 'Неверный формат';
            }
        }

        // Проверка минимальной длины
        if (isValid && minLength && field.value.trim().length < parseInt(minLength)) {
            isValid = false;
            errorMessage = `Минимальная длина: ${minLength} символов`;
        }

        // Проверка максимальной длины
        if (isValid && maxLength && field.value.trim().length > parseInt(maxLength)) {
            isValid = false;
            errorMessage = `Максимальная длина: ${maxLength} символов`;
        }

        // Обновить стили поля
        this.updateFieldState(field, isValid, errorMessage);

        return isValid;
    }

    /**
     * Обновление состояния поля
     * @param {HTMLElement} field - Поле формы
     * @param {boolean} isValid - Валидно ли поле
     * @param {string} errorMessage - Сообщение об ошибке
     */
    updateFieldState(field, isValid, errorMessage) {
        const wrapper = field.closest('.form__field') || field.parentElement;
        const errorElement = wrapper.querySelector('.form__error');

        if (isValid) {
            field.classList.remove('form__field--error');
            field.classList.add('form__field--valid');
            
            if (errorElement) {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
            }
        } else {
            field.classList.remove('form__field--valid');
            field.classList.add('form__field--error');
            
            if (errorElement) {
                errorElement.textContent = errorMessage;
                errorElement.style.display = 'block';
            } else if (errorMessage) {
                const error = document.createElement('div');
                error.className = 'form__error';
                error.textContent = errorMessage;
                error.style.color = 'var(--color-danger)';
                error.style.fontSize = 'var(--font-size-sm)';
                error.style.marginTop = 'var(--spacing-xs)';
                wrapper.appendChild(error);
            }
        }
    }

    /**
     * Обработка отправки формы
     * @param {HTMLFormElement} form - Форма
     * @param {Event} e - Событие
     */
    handleSubmit(form, e) {
        e.preventDefault();

        let isValid = true;

        // Валидация всех полей
        const fields = form.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        if (!isValid) {
            console.warn('CHOKERZ: Форма содержит ошибки');
            return;
        }

        // Отправка формы
        this.submitForm(form);
    }

    /**
     * Отправка формы
     * @param {HTMLFormElement} form - Форма
     */
    submitForm(form) {
        const submitBtn = form.querySelector('[type="submit"]');
        
        // Блокировка кнопки
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Отправка...';
        }

        // Отправка через Fetch API или стандартная отправка
        if (form.hasAttribute('data-ajax')) {
            this.ajaxSubmit(form);
        } else {
            form.submit();
        }
    }

    /**
     * AJAX отправка формы
     * @param {HTMLFormElement} form - Форма
     */
    async ajaxSubmit(form) {
        const formData = new FormData(form);
        const action = form.getAttribute('action') || window.location.href;
        const method = form.getAttribute('method') || 'POST';

        try {
            const response = await fetch(action, {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                this.handleSuccess(form);
            } else {
                this.handleError(form, 'Ошибка при отправке формы');
            }
        } catch (error) {
            this.handleError(form, 'Ошибка соединения');
            console.error('CHOKERZ: Ошибка отправки формы', error);
        }
    }

    /**
     * Обработка успешной отправки
     * @param {HTMLFormElement} form - Форма
     */
    handleSuccess(form) {
        console.log('CHOKERZ: Форма успешно отправлена');

        // Очистка формы
        form.reset();

        // Сброс состояния полей
        const fields = form.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            field.classList.remove('form__field--valid', 'form__field--error');
        });

        // Показать сообщение об успехе
        const successMessage = form.getAttribute('data-success-message') || 'Форма успешно отправлена!';
        
        const successElement = document.createElement('div');
        successElement.className = 'form__success';
        successElement.textContent = successMessage;
        successElement.style.color = 'var(--color-success)';
        successElement.style.padding = 'var(--spacing-md)';
        successElement.style.marginBottom = 'var(--spacing-md)';
        successElement.style.borderRadius = 'var(--radius-md)';
        successElement.style.backgroundColor = 'rgba(40, 167, 69, 0.1)';

        form.prepend(successElement);

        // Убрать сообщение через 3 секунды
        setTimeout(() => {
            successElement.remove();
        }, 3000);
    }

    /**
     * Обработка ошибки отправки
     * @param {HTMLFormElement} form - Форма
     * @param {string} message - Сообщение об ошибке
     */
    handleError(form, message) {
        console.error('CHOKERZ: Ошибка отправки формы', message);

        const submitBtn = form.querySelector('[type="submit"]');
        
        // Разблокировка кнопки
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = submitBtn.getAttribute('data-original-text') || 'Отправить';
        }

        // Показать сообщение об ошибке
        const errorElement = document.createElement('div');
        errorElement.className = 'form__error';
        errorElement.textContent = message;
        errorElement.style.color = 'var(--color-danger)';
        errorElement.style.padding = 'var(--spacing-md)';
        errorElement.style.marginBottom = 'var(--spacing-md)';
        errorElement.style.borderRadius = 'var(--radius-md)';
        errorElement.style.backgroundColor = 'rgba(220, 53, 69, 0.1)';

        form.prepend(errorElement);
    }
}

// Экспорт модуля
const forms = new Forms();
export default forms;
