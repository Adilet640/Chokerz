(function initColorSwatches() {
    'use strict';

    // Обрабатываем свотчи только у последней добавленной карточки
    // (если компонент в списке — все карточки появляются последовательно)
    const swatches = document.querySelectorAll('.product-card__color-swatch[data-color]');

    swatches.forEach(function (swatch) {
        const hex = swatch.dataset.color;
        if (hex && /^#?[0-9A-Fa-f]{3,8}$/.test(hex)) {
            // Добавляем # если отсутствует
            const color = hex.startsWith('#') ? hex : '#' + hex;
            // CSS custom property — не inline style, а переменная
            swatch.style.setProperty('--color-swatch', color);
            swatch.classList.add('product-card__color-swatch--loaded');
        }
    });
}());
</script>
