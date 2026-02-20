/**
 * Модуль избранного (Wishlist)
 * 
 * @package CHOKERZ
 * @version 1.0.0
 */

class Wishlist {
    constructor() {
        this.wishlistBtns = null;
        this.wishlistCountEl = null;
        this.wishlistItems = [];
        this.initComplete = false;
    }

    /**
     * Инициализация модуля
     */
    init() {
        this.wishlistBtns = document.querySelectorAll('[data-wishlist-toggle]');
        this.wishlistCountEl = document.querySelector('[data-wishlist-count]');

        // Загрузка данных из localStorage
        this.loadWishlist();

        if (this.wishlistBtns.length > 0) {
            this.bindEvents();
        }

        this.updateWishlistCount();
        this.initComplete = true;

        console.log('CHOKERZ: Избранное инициализировано');

        return this;
    }

    /**
     * Привязка событий
     */
    bindEvents() {
        // Переключение товара в избранном
        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-wishlist-toggle]')) {
                e.preventDefault();
                const btn = e.target.closest('[data-wishlist-toggle]');
                const productId = btn.dataset.productId;
                const productName = btn.dataset.productName || 'Товар';
                const productPrice = parseFloat(btn.dataset.productPrice) || 0;
                const productImage = btn.dataset.productImage || '';

                this.toggleItem({
                    id: productId,
                    name: productName,
                    price: productPrice,
                    image: productImage
                }, btn);
            }
        });
    }

    /**
     * Переключить товар в избранном
     * @param {Object} item - Товар
     * @param {Element} btn - Кнопка
     */
    toggleItem(item, btn) {
        const isFavorite = this.isInWishlist(item.id);

        if (isFavorite) {
            this.removeItem(item.id);
            this.updateButtonState(btn, false);
            this.showNotification('Товар удален из избранного', 'warning');
        } else {
            this.addItem(item);
            this.updateButtonState(btn, true);
            this.showNotification('Товар добавлен в избранное', 'success');
        }
    }

    /**
     * Добавить товар в избранное
     * @param {Object} item - Товар
     */
    addItem(item) {
        // Проверка на дубликат
        if (!this.isInWishlist(item.id)) {
            this.wishlistItems.push(item);
            this.saveWishlist();
            this.updateWishlistCount();
        }

        console.log('CHOKERZ: Товар добавлен в избранное', item);
    }

    /**
     * Удалить товар из избранного
     * @param {string} itemId - ID товара
     */
    removeItem(itemId) {
        this.wishlistItems = this.wishlistItems.filter(item => item.id !== itemId);
        this.saveWishlist();
        this.updateWishlistCount();

        // Обновить все кнопки с этим товаром
        const buttons = document.querySelectorAll(`[data-wishlist-toggle][data-product-id="${itemId}"]`);
        buttons.forEach(btn => {
            this.updateButtonState(btn, false);
        });

        console.log('CHOKERZ: Товар удален из избранного', itemId);
    }

    /**
     * Проверить наличие товара в избранном
     * @param {string} itemId - ID товара
     * @returns {boolean}
     */
    isInWishlist(itemId) {
        return this.wishlistItems.some(item => item.id === itemId);
    }

    /**
     * Обновить состояние кнопки
     * @param {Element} btn - Кнопка
     * @param {boolean} isActive - Активна ли
     */
    updateButtonState(btn, isActive) {
        if (isActive) {
            btn.classList.add('wishlist-btn--active');
            btn.setAttribute('aria-pressed', 'true');
            btn.title = 'Удалить из избранного';
        } else {
            btn.classList.remove('wishlist-btn--active');
            btn.setAttribute('aria-pressed', 'false');
            btn.title = 'Добавить в избранное';
        }
    }

    /**
     * Обновить счетчик избранного
     */
    updateWishlistCount() {
        const count = this.wishlistItems.length;

        if (this.wishlistCountEl) {
            if (count > 0) {
                this.wishlistCountEl.textContent = count;
                this.wishlistCountEl.style.display = 'block';
            } else {
                this.wishlistCountEl.style.display = 'none';
            }
        }
    }

    /**
     * Сохранить избранное в localStorage
     */
    saveWishlist() {
        localStorage.setItem('chokerz_wishlist', JSON.stringify(this.wishlistItems));
    }

    /**
     * Загрузить избранное из localStorage
     */
    loadWishlist() {
        const savedWishlist = localStorage.getItem('chokerz_wishlist');
        
        if (savedWishlist) {
            try {
                this.wishlistItems = JSON.parse(savedWishlist);
            } catch (e) {
                console.error('CHOKERZ: Ошибка загрузки избранного', e);
                this.wishlistItems = [];
            }
        }
    }

    /**
     * Показать уведомление
     * @param {string} message - Сообщение
     * @param {string} type - Тип (success, error, warning)
     */
    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification notification--${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background-color: ${type === 'success' ? '#28A745' : '#FFC107'};
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            animation: slideIn 0.3s ease;
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    /**
     * Проверка инициализации
     */
    isInitialized() {
        return this.initComplete;
    }
}

// Создание экземпляра и экспорт
const wishlist = new Wishlist();

export default wishlist;
