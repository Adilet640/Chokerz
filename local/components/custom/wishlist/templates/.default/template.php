<?php
/**
 * Шаблон компонента избранного (кастомный)
 * 
 * @author VibePilot
 * @version 1.0
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div class="wishlist" data-component="wishlist">
    <?php if ($arResult['NEED_AUTH']): ?>
        <div class="wishlist__empty">
            <div class="wishlist__empty-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <p class="wishlist__empty-text">Авторизуйтесь, чтобы добавлять товары в избранное</p>
            <a href="/auth/" class="btn btn--primary">Войти</a>
        </div>
    <?php elseif (empty($arResult['ITEMS'])): ?>
        <div class="wishlist__empty">
            <div class="wishlist__empty-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <p class="wishlist__empty-text">Ваше избранное пусто</p>
            <a href="/catalog/" class="btn btn--primary">Выбрать товары</a>
        </div>
    <?php else: ?>
        <!-- Список товаров из избранного (заглушка) -->
        <div class="wishlist__list">
            <p class="wishlist__list-count">В избранном: <span><?= count($arResult['ITEMS']) ?></span> товаров</p>
            <div class="wishlist__items">
                <!-- Здесь будет вывод товаров из избранного через цикл -->
                <p>Товары из избранного будут отображены здесь после реализации логики</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// JavaScript для избранного
document.addEventListener('DOMContentLoaded', function() {
    const wishlist = document.querySelector('[data-component="wishlist"]');
    
    if (!wishlist) return;

    // Функция добавления товара в избранное (глобальная, для использования из других компонентов)
    window.addToWishlist = function(productId) {
        if (!wishlist) return;

        const userId = <?= (int)$arResult['USER_ID'] ?>;
        
        if (!userId) {
            // Пользователь не авторизован, показываем сообщение или перенаправляем на авторизацию
            alert('Пожалуйста, авторизуйтесь для добавления в избранное');
            window.location.href = '/auth/';
            return;
        }

        // AJAX запрос для добавления в избранное (заглушка)
        fetch('/local/ajax/wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'add',
                productId: productId,
                userId: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Обновление состояния кнопки или счетчика избранного (если реализовано)
                console.log('Товар добавлен в избранное');
            } else {
                alert('Ошибка при добавлении в избранное: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при добавлении в избранное');
        });
    };
});
</script>
