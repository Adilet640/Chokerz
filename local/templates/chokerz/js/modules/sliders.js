/**
 * Модуль: Слайдеры и карусели
 * 
 * @package CHOKERZ
 * @version 1.0.0
 */

class Sliders {
    constructor() {
        this.sliders = [];
    }

    /**
     * Инициализация модуля
     */
    init() {
        console.log('CHOKERZ: Инициализация слайдеров');
        
        this.initSliders();
        
        return this;
    }

    /**
     * Инициализация всех слайдеров
     */
    initSliders() {
        const sliders = document.querySelectorAll('[data-slider]');
        
        sliders.forEach(slider => {
            const id = slider.getAttribute('data-slider');
            const slides = slider.querySelectorAll('[data-slide]');
            const prevBtn = slider.querySelector('[data-slider-prev]');
            const nextBtn = slider.querySelector('[data-slider-next]');
            const dotsContainer = slider.querySelector('[data-slider-dots]');
            
            const sliderInstance = {
                id,
                element: slider,
                slides,
                prevBtn,
                nextBtn,
                dotsContainer,
                currentIndex: 0,
                autoPlay: slider.hasAttribute('data-slider-auto'),
                interval: null
            };
            
            this.sliders.push(sliderInstance);
            
            // Инициализация слайдера
            this.setupSlider(sliderInstance);
        });
    }

    /**
     * Настройка слайдера
     * @param {Object} slider - Экземпляр слайдера
     */
    setupSlider(slider) {
        // Показать первый слайд
        this.showSlide(slider, 0);
        
        // Навигация
        if (slider.prevBtn) {
            slider.prevBtn.addEventListener('click', () => {
                this.prevSlide(slider);
            });
        }
        
        if (slider.nextBtn) {
            slider.nextBtn.addEventListener('click', () => {
                this.nextSlide(slider);
            });
        }
        
        // Точки навигации
        if (slider.dotsContainer) {
            this.createDots(slider);
        }
        
        // Автопрокрутка
        if (slider.autoPlay) {
            this.startAutoPlay(slider);
            
            // Остановка автопрокрутки при наведении
            slider.element.addEventListener('mouseenter', () => {
                this.stopAutoPlay(slider);
            });
            
            slider.element.addEventListener('mouseleave', () => {
                this.startAutoPlay(slider);
            });
        }
    }

    /**
     * Показать слайд
     * @param {Object} slider - Экземпляр слайдера
     * @param {number} index - Индекс слайда
     */
    showSlide(slider, index) {
        // Скрыть все слайды
        slider.slides.forEach(slide => {
            slide.style.display = 'none';
        });
        
        // Показать текущий слайд
        slider.slides[index].style.display = 'block';
        slider.currentIndex = index;
        
        // Обновить точки навигации
        if (slider.dotsContainer) {
            this.updateDots(slider);
        }
        
        // Событие смены слайда
        const event = new CustomEvent('slider:changed', { 
            detail: { id: slider.id, index } 
        });
        slider.element.dispatchEvent(event);
    }

    /**
     * Следующий слайд
     * @param {Object} slider - Экземпляр слайдера
     */
    nextSlide(slider) {
        let nextIndex = slider.currentIndex + 1;
        
        if (nextIndex >= slider.slides.length) {
            nextIndex = 0;
        }
        
        this.showSlide(slider, nextIndex);
    }

    /**
     * Предыдущий слайд
     * @param {Object} slider - Экземпляр слайдера
     */
    prevSlide(slider) {
        let prevIndex = slider.currentIndex - 1;
        
        if (prevIndex < 0) {
            prevIndex = slider.slides.length - 1;
        }
        
        this.showSlide(slider, prevIndex);
    }

    /**
     * Создание точек навигации
     * @param {Object} slider - Экземпляр слайдера
     */
    createDots(slider) {
        slider.dotsContainer.innerHTML = '';
        
        slider.slides.forEach((_, index) => {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'slider__dot';
            dot.setAttribute('aria-label', `Слайд ${index + 1}`);
            
            if (index === 0) {
                dot.classList.add('slider__dot--active');
            }
            
            dot.addEventListener('click', () => {
                this.showSlide(slider, index);
            });
            
            slider.dotsContainer.appendChild(dot);
        });
    }

    /**
     * Обновление точек навигации
     * @param {Object} slider - Экземпляр слайдера
     */
    updateDots(slider) {
        const dots = slider.dotsContainer.querySelectorAll('.slider__dot');
        
        dots.forEach((dot, index) => {
            if (index === slider.currentIndex) {
                dot.classList.add('slider__dot--active');
            } else {
                dot.classList.remove('slider__dot--active');
            }
        });
    }

    /**
     * Запуск автопрокрутки
     * @param {Object} slider - Экземпляр слайдера
     */
    startAutoPlay(slider) {
        this.stopAutoPlay(slider);
        
        slider.interval = setInterval(() => {
            this.nextSlide(slider);
        }, 5000); // 5 секунд
    }

    /**
     * Остановка автопрокрутки
     * @param {Object} slider - Экземпляр слайдера
     */
    stopAutoPlay(slider) {
        if (slider.interval) {
            clearInterval(slider.interval);
            slider.interval = null;
        }
    }

    /**
     * Получение слайдера по ID
     * @param {string} id - ID слайдера
     * @returns {Object|null}
     */
    getSlider(id) {
        return this.sliders.find(slider => slider.id === id) || null;
    }
}

// Экспорт модуля
const sliders = new Sliders();
export default sliders;
