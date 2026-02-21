/**
 * Модуль: Lazy Load изображений
 * 
 * @package CHOKERZ
 * @version 1.0.0
 */

class LazyLoad {
    constructor() {
        this.images = [];
        this.observer = null;
    }

    /**
     * Инициализация модуля
     */
    init() {
        console.log('CHOKERZ: Инициализация lazy load');
        
        // Проверка поддержки IntersectionObserver
        if (!('IntersectionObserver' in window)) {
            console.warn('CHOKERZ: IntersectionObserver не поддерживается, загружаем все изображения');
            this.loadAllImages();
            return this;
        }
        
        this.initLazyLoad();
        
        return this;
    }

    /**
     * Инициализация lazy load
     */
    initLazyLoad() {
        // Найти все изображения с атрибутом data-src
        this.images = Array.from(document.querySelectorAll('img[data-src]'));
        
        if (this.images.length === 0) {
            console.log('CHOKERZ: Нет изображений для lazy load');
            return;
        }
        
        // Создать наблюдатель
        this.observer = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadImage(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            },
            {
                rootMargin: '50px 0px', // Загружать изображения за 50px до появления
                threshold: 0.01
            }
        );
        
        // Наблюдать за всеми изображениями
        this.images.forEach(img => {
            this.observer.observe(img);
        });
    }

    /**
     * Загрузка изображения
     * @param {HTMLImageElement} img - Изображение
     */
    loadImage(img) {
        const src = img.getAttribute('data-src');
        
        if (!src) {
            console.warn('CHOKERZ: Изображение не имеет атрибута data-src');
            return;
        }
        
        // Создать новый объект изображения для предварительной загрузки
        const image = new Image();
        
        // Обработчик успешной загрузки
        image.onload = () => {
            img.src = src;
            
            // Удалить атрибут после загрузки
            img.removeAttribute('data-src');
            
            // Добавить класс для анимации
            img.classList.add('lazy-loaded');
            
            console.log(`CHOKERZ: Изображение загружено: ${src}`);
        };
        
        // Обработчик ошибки
        image.onerror = () => {
            console.error(`CHOKERZ: Ошибка загрузки изображения: ${src}`);
            
            // Загрузить резервное изображение или оставить как есть
            if (img.hasAttribute('data-src-fallback')) {
                img.src = img.getAttribute('data-src-fallback');
                img.removeAttribute('data-src');
            }
        };
        
        // Начать загрузку
        image.src = src;
    }

    /**
     * Загрузить все изображения (для старых браузеров)
     */
    loadAllImages() {
        const images = document.querySelectorAll('img[data-src]');
        
        images.forEach(img => {
            const src = img.getAttribute('data-src');
            
            if (src) {
                img.src = src;
                img.removeAttribute('data-src');
            }
        });
    }

    /**
     * Обновить наблюдателя (например, после добавления новых изображений)
     */
    refresh() {
        if (!this.observer) return;
        
        // Остановить текущего наблюдателя
        this.images.forEach(img => {
            if (img.hasAttribute('data-src')) {
                this.observer.unobserve(img);
            }
        });
        
        // Инициализировать заново
        this.initLazyLoad();
    }

    /**
     * Остановить наблюдателя
     */
    destroy() {
        if (!this.observer) return;
        
        this.images.forEach(img => {
            this.observer.unobserve(img);
        });
        
        this.observer.disconnect();
        this.observer = null;
    }
}

// Экспорт модуля
const lazyLoad = new LazyLoad();
export default lazyLoad;
