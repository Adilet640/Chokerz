# AJAX обработчики (заглушки для будущей реализации)

## Описание:
Данные файлы являются заглушками для AJAX обработчиков функционала сайта. Они содержат примеры кода для обработки запросов от клиентской части (через JavaScript).

## Обработчики:

### 1. Избранное (`wishlist.php`)
- Действия: `add`, `remove`
- Параметры: `productId`, `userId`
- Ответ: JSON с результатом операции

### 2. Корзина (`cart.php`)
- Действия: `add`, `remove`, `update`, `get`
- Параметры: `productId`, `quantity`
- Ответ: JSON с результатом операции

## Инструкция по использованию:
1. Настройте HL-блок "Избранное" в админке (если используется)
2. Настройте модуль "Продажи" в админке (для корзины)
3. Протестируйте обработчики через AJAX запросы из браузера или Postman

## Примеры запросов:
```javascript
// Добавление в избранное (через JavaScript)
fetch('/local/ajax/wishlist.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        action: 'add',
        productId: 123,
        userId: 456
    })
})
.then(response => response.json())
.then(data => console.log(data));

// Добавление в корзину (через JavaScript)
fetch('/local/ajax/cart.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        action: 'add',
        productId: 123,
        quantity: 1
    })
})
.then(response => response.json())
.then(data => console.log(data));
```
