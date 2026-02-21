# Компонент карточки товара (кастомный)

catalog.item — кастомный компонент карточки товара для интернет-магазина "CHOKERZ — амуниция для животных".

## Параметры компонента:

- ELEMENT_ID (int) — ID элемента каталога
- CACHE_TIME (int) — время кэширования

## Пример использования:

```php
<?php
$APPLICATION->IncludeComponent(
    "custom:catalog.item",
    ".default",
    Array(
        "ELEMENT_ID" => 123,
        "CACHE_TIME" => "3600"
    )
);
?>
```
