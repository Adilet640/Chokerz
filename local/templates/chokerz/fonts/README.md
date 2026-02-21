# Инструкция по установке шрифтов

## Требуемые шрифты

1. **Montserrat** (основной шрифт)
   - Веса: 300, 400, 500, 600, 700
   - Скачать: https://fonts.google.com/specimen/Montserrat
   - Формат: WOFF2

2. **Inter** (вторичный шрифт)
   - Веса: 300, 400, 500, 600, 700
   - Скачать: https://fonts.google.com/specimen/Inter
   - Формат: WOFF2

3. **IBM Plex Sans** (для логотипа)
   - Веса: 400, 500, 600, 700
   - Скачать: https://fonts.google.com/specimen/IBM+Plex+Sans
   - Формат: WOFF2

## Как установить шрифты

### Вариант 1: Использование сервиса Google Fonts (рекомендуется для разработки)

1. Откройте файл `local/templates/chokerz/header.php`
2. Найдите секцию `<head>`
3. Добавьте следующие ссылки:

```html
<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
```

4. В файле `local/templates/chokerz/styles/base/typography.css` закомментируйте блоки `@font-face` (строки 5-45)

### Вариант 2: Самостоятельная загрузка шрифтов (для продакшена)

1. Перейдите на сайт [google-webfonts-helper](https://google-webfonts-helper.herokuapp.com/fonts)
2. Выберите нужные шрифты и веса
3. Скачайте архив с шрифтами в формате WOFF2
4. Распакуйте файлы в папку `local/templates/chokerz/fonts/`
5. Убедитесь, что имена файлов совпадают с указанными в `typography.css`:

```
fonts/
├── montserrat-v25-latin_cyrillic-300.woff2
├── montserrat-v25-latin_cyrillic-regular.woff2
├── montserrat-v25-latin_cyrillic-500.woff2
├── montserrat-v25-latin_cyrillic-600.woff2
├── montserrat-v25-latin_cyrillic-700.woff2
├── inter-v13-latin_cyrillic-300.woff2
├── inter-v13-latin_cyrillic-regular.woff2
├── inter-v13-latin_cyrillic-500.woff2
├── inter-v13-latin_cyrillic-600.woff2
├── inter-v13-latin_cyrillic-700.woff2
├── ibm-plex-sans-v14-latin_cyrillic-regular.woff2
├── ibm-plex-sans-v14-latin_cyrillic-500.woff2
├── ibm-plex-sans-v14-latin_cyrillic-600.woff2
└── ibm-plex-sans-v14-latin_cyrillic-700.woff2
```

## Проверка установки

После установки шрифтов:
1. Откройте сайт в браузере
2. Нажмите F12 → вкладка Network → Filter: Font
3. Убедитесь, что шрифты загружаются без ошибок

## Важно

- Для **разработки** рекомендуется использовать **Вариант 1** (Google Fonts CDN)
- Для **продакшена** используйте **Вариант 2** (локальные шрифты) для лучшей производительности
- Все шрифты должны поддерживать **кириллицу** (Cyrillic subset)
