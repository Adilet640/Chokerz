/**
 * Модуль корзины
 * 
 * @package CHOKERZ
 * @version 1.0.0
 */

class Cart {
    constructor() {
        this.cartBtn = null;
        this.cartCountEl = null;
        this.cartItems = [];
        this.initComplete = false;
    }

    /**
     * Инициализация модуля
     */
    init() {
        this.cartBtn = document.querySelector('.actions__btn--cart');
        this.cartCountEl = document.querySelector('[data-cart-count]');

        // Загрузка данных из localStorage
        this.loadCart();

        if (this.cartBtn) {
            this.bindEvents();
        }

        this.updateCartCount();
        this.initComplete = true;

        console.log('CHOKERZ: Корзина инициализирована');

        return this;
    }

    /**
     * Привязка событий
     */
    bindEvents() {
        // Добавление товара в корзину
        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-add-to-cart]')) {
                e.preventDefault();
                const btn = e.target.closest('[data-add-to-cart]');
                const productId = btn.dataset.productId;
                const productName = btn.dataset.productName || 'Товар';
                const productPrice = parseFloat(btn.dataset.productPrice) || 0;
                const productImage = btn.dataset.productImage || '';

                this.addItem({
                    id: productId,
                    name: productName,
                    price: productPrice,
                    image: productImage,
                    quantity: 1
                });
            }
        });
    }

    /**
     * Добавить товар в корзину
     * @param {Object} item - Товар
     */
    addItem(item) {
        // Проверка наличия товара в корзине
        const existingItem = this.cartItems.find(i => i.id === item.id);

        if (existingItem) {
            existingItem.quantity += item.quantity || 1;
        } else {
            this.cartItems.push(item);
        }

        // Сохранение в localStorage
        this.saveCart();

        // Обновление счетчика
        this.updateCartCount();

        // Показать уведомление
        this.showNotification('Товар добавлен в корзину', 'success');

        console.log('CHOKERZ: Товар добавлен в корзину', item);
    }

    /**
     * Удалить товар из корзины
     * @param {string} itemId - ID товара
     */
    removeItem(itemId) {
        this.cartItems = this.cartItems.filter(item => item.id !== itemId);
        this.saveCart();
        this.updateCartCount();

        console.log('CHOKERZ: Товар удален из корзины', itemId);
    }

    /**
     * Обновить количество товара
     * @param {string} itemId - ID товара
     * @param {number} quantity - Количество
     */
    updateQuantity(itemId, quantity) {
        const item = this.cartItems.find(i => i.id === itemId);

        if (item) {
            item.quantity = Math.max(1, quantity);
            this.saveCart();
        }

        console.log('CHOKERZ: Количество товара обновлено', itemId, quantity);
    }

    /**
     * Очистить корзину
     */
    clear() {
        this.cartItems = [];
        this.saveCart();
        this.updateCartCount();

        console.log('CHOKERZ: Корзина очищена');
    }

    /**
     * Получить общее количество товаров
     * @returns {number}
     */
    getTotalItems() {
        return this.cartItems.reduce((total, item) => total + item.quantity, 0);
    }

    /**
     * Получить общую стоимость
     * @returns {number}
     */
    getTotalPrice() {
        return this.cartItems.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    /**
     * Обновить счетчик товаров
     */
    updateCartCount() {
        const count = this.getTotalItems();

        if (this.cartCountEl) {
            if (count > 0) {
                this.cartCountEl.textContent = count;
                this.cartCountEl.style.display = 'block';
            } else {
                this.cartCountEl.style.display = 'none';
            }
        }
    }

    /**
     * Сохранить корзину в localStorage
     */
    saveCart() {
        localStorage.setItem('chokerz_cart', JSON.stringify(this.cartItems));
    }

    /**
     * Загрузить корзину из localStorage
     */
    loadCart() {
        const savedCart = localStorage.getItem('chokerz_cart');
        
        if (savedCart) {
            try {
                this.cartItems = JSON.parse(savedCart);
            } catch (e) {
                console.error('CHOKERZ: Ошибка загрузки корзины', e);
                this.cartItems = [];
            }
        }
    }

    /**
     * Показать уведомление
     * @param {string} message - Сообщение
     * @param {string} type - Тип (success, error, warning)
     */
    showNotification(message, type = 'success') {
        // Создание уведомления
        const notification = document.createElement('div');
        notification.className = `notification notification--${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background-color: ${type === 'success' ? '#28A745' : '#DC3545'};
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            animation: slideIn 0.3s ease;
        `;

        // Добавление в документ
        document.body.appendChild(notification);

        // Удаление через 3 секунды
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);

        // Добавление анимаций
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Проверка инициализации
     */
    isInitialized() {
        return this.initComplete;
    }
}

// Создание экземпляра и экспорт
const cart = new Cart();

export default cart;
