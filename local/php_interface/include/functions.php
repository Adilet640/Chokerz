<?php
/**
 * Кастомные функции проекта CHOKERZ
 */

/**
 * Форматирование цены
 */
function formatPrice($price) {
    return number_format($price, 0, '', ' ') . ' ₽';
}

/**
 * Получение первых букв слов для инициалов
 */
function getInitials($name) {
    $parts = explode(' ', $name);
    $initials = '';
    
    foreach ($parts as $part) {
        if (!empty($part)) {
            $initials .= mb_substr($part, 0, 1);
        }
    }
    
    return $initials;
}

/**
 * Форматирование даты
 */
function formatDate($date, $format = 'd.m.Y') {
    if (empty($date)) {
        return '';
    }
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Обрезка текста с сохранением целостности слов
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    
    $text = mb_substr($text, 0, $length);
    $lastSpace = mb_strrpos($text, ' ');
    
    if ($lastSpace !== false) {
        $text = mb_substr($text, 0, $lastSpace);
    }
    
    return $text . $suffix;
}

/**
 * Генерация ЧПУ из строки
 */
function generateSlug($string) {
    $string = mb_strtolower($string, 'UTF-8');
    
    // Замена кириллицы на латиницу
    $cyr = ['а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'];
    $lat = ['a', 'b', 'v', 'g', 'd', 'e', 'e', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', '', 'y', '', 'e', 'yu', 'ya'];
    
    $string = str_replace($cyr, $lat, $string);
    
    // Удаление всего кроме букв, цифр, дефисов и подчеркиваний
    $string = preg_replace('/[^a-z0-9_-]/', '-', $string);
    
    // Удаление множественных дефисов
    $string = preg_replace('/-+/', '-', $string);
    
    // Удаление дефисов в начале и конце
    $string = trim($string, '-');
    
    return $string;
}

/**
 * Проверка на мобильное устройство
 */
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

/**
 * Получение текущего URL
 */
function getCurrentUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    
    return $protocol . $host . $uri;
}
