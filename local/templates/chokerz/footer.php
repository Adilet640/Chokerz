<?php
/**
 * Footer шаблона сайта CHOKERZ
 *
 * Структура:
 *   .footer            — тёмно-синий подвал
 *     .footer__top     — логотип + колонки навигации + контакты
 *     .footer__bottom  — копирайт + соцсети + правовые ссылки
 *
 * Закрываем тег <main>, открытый в header.php.
 *
 * @package chokerz
 * @since   1.0.0
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$currentYear = date('Y');
?>

</main><!-- /main: открыт в header.php -->

<!-- ════════════════════════════════════════════════════════════════════════════
     FOOTER — подвал сайта
     ════════════════════════════════════════════════════════════════════════════ -->
<footer class="footer" role="contentinfo">
    <div class="footer__container container">

        <!-- ── TOP: бренд + навигация + маркетплейсы ──────────────────────── -->
        <div class="footer__top">

            <!-- Логотип + описание -->
            <div class="footer__brand">
                <a href="/" class="footer__logo" aria-label="CHOKERZ — перейти на главную">
                    <!--
                        Inline SVG логотипа в footer:
                        fill="currentColor" — белый цвет наследуется от .footer__logo через CSS
                    -->
                    <svg class="footer__logo-icon"
                         xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 204.8 153.6"
                         width="56" height="42"
                         fill="currentColor"
                         aria-hidden="true"
                         focusable="false">
                        <g transform="translate(0,153.6) scale(0.01,-0.01)" stroke="none">
                            <path d="M10185 12489c-1093-71-2068-548-2779-1359-544-619-881-1402-967-2240-17-162-17-600 0-760 67-654 279-1259 627-1793 346-529 789-951 1339-1274 523-307 1157-498 1758-531 72-4 977-6 2010-4l1877 3 0 645 0 644-1892 0c-1559 0-1915 3-2018 14-814 92-1532 537-1967 1221-511 802-556 1821-117 2664 135 260 261 429 483 652 237 237 423 371 706 510 237 116 413 176 650 224 239 48 193 47 2228 45l1927-1 0 670 0 671-1839 0c-1011 0-1851 2-1867 4-16 2-87 0-159-5z"/>
                            <path d="M6825 3941c-196-57-359-234-402-435-47-227 79-472 300-581 209-103 457-73 616 74 36 33 41 42 32 59-6 11-57 63-115 116l-105 98-43-30c-53-36-77-44-137-44-59 1-90 13-135 53-104 91-105 236-2 326 46 41 96 56 166 51 41-3 70-12 99-31 22-15 46-27 53-27 7 0 63 47 123 104l110 104-47 45c-58 54-145 101-223 122-78 20-214 18-290-4z"/>
                            <path d="M9123 3946c-191-46-370-223-412-406-16-68-13-209 5-271 42-144 151-274 285-342 170-85 337-86 506-2 201 100 313 273 313 484 0 160-46 267-161 381-82 81-161 128-264 154-70 19-199 19-272 2zm233-335c151-69 172-281 36-375-147-102-352 2-352 179 0 157 170 262 316 196z"/>
                            <path d="M7541 3936c-8-9-10-157-9-532l3-519 165 0 165 0 3 203 2 202 155 0 155 0 2-202 3-203 160-3c142-2 161-1 172 15 10 13 13 132 13 519 0 444-2 504-16 518-13 13-42 16-163 16-101 0-151-4-159-12-8-8-12-60-12-170l0-158-155 0-155 0 0 164c0 140-2 165-16 170-9 3-80 6-159 6-108 0-145-3-154-14z"/>
                            <path d="M10001 3936c-8-9-10-157-9-532l3-519 168-3c92-1 168-1 169 0 1 2 4 98 7 215l6 211 114-201c63-111 122-207 130-214 19-15 381-18 381-4 0 5-70 131-155 280l-155 271 150 242c83 133 150 248 150 255 0 10-42 13-184 13-178 0-186-1-210-22-14-13-71-99-126-193l-100-170 0 177c0 147-3 179-16 192-21 22-305 24-323 2z"/>
                            <path d="M11127 3943c-4-3-7-64-7-135l0-128 405 0 406 0-3 133-3 132-396 3c-217 1-398-1-402-5z"/>
                            <path d="M12120 3931c-11-21-15-1007-4-1035 9-24 329-24 338 0 3 9 6 85 6 170 0 209 7 208 120-14l85-167 168-3c141-2 168 0 173 13 3 8-38 95-95 201-56 102-101 188-101 191 0 3 22 25 49 49 29 26 62 69 78 102 25 51 28 68 28 157 0 91-3 105-29 158-33 63-93 125-153 155-65 34-146 42-404 42-235 0-249-1-259-19zm495-281c35-33 35-88-1-121-22-20-35-24-92-24l-67 0-3 74c-4 104-4 104 73 99 50-3 70-9 90-28z"/>
                            <path d="M13162 3938c-13-13-18-232-6-262 5-14 35-16 225-16 120 0 219-3 219-6 0-3-99-127-220-275l-221-270 3-112 3-112 430-3c395-2 431-1 443 15 17 24 17 247-1 262-9 7-91 12-230 13l-217 3 225 279 225 278 0 103c0 85-3 104-16 109-9 3-203 6-433 6-311 0-420-3-429-12z"/>
                            <path d="M11122 3408l3-133 400 0 400 0 3 133 3 132-406 0-406 0 3-132z"/>
                            <path d="M11127 3143c-4-3-7-64-7-135l0-128 405 0 406 0-3 133-3 132-396 3c-217 1-398-1-402-5z"/>
                        </g>
                    </svg>
                    <span class="footer__logo-text">CHOKERZ</span>
                </a>
                <p class="footer__tagline">Амуниция для ваших питомцев — качество и стиль</p>

                <!-- Маркетплейсы -->
                <div class="footer__marketplaces">
                    <a href="https://www.ozon.ru/seller/10238"
                       class="footer__marketplace-link footer__marketplace-link--ozon"
                       target="_blank" rel="noopener noreferrer"
                       aria-label="Купить на Ozon">
                        OZON
                    </a>
                    <a href="https://www.wildberries.ru/seller/48237"
                       class="footer__marketplace-link footer__marketplace-link--wb"
                       target="_blank" rel="noopener noreferrer"
                       aria-label="Купить на Wildberries">
                        WB
                    </a>
                </div>
            </div>

            <!-- Навигационные колонки -->
            <div class="footer__nav-grid">

                <nav class="footer__nav-col" aria-label="Каталог">
                    <p class="footer__nav-title">Каталог</p>
                    <ul class="footer__nav-list" role="list">
                        <li><a href="/catalog/" class="footer__nav-link">Все товары</a></li>
                        <li><a href="/catalog/ошейники/" class="footer__nav-link">Ошейники</a></li>
                        <li><a href="/catalog/поводки/" class="footer__nav-link">Поводки</a></li>
                        <li><a href="/catalog/шлейки/" class="footer__nav-link">Шлейки</a></li>
                        <li><a href="/wholesale/" class="footer__nav-link">Оптовым покупателям</a></li>
                    </ul>
                </nav>

                <nav class="footer__nav-col" aria-label="Компания">
                    <p class="footer__nav-title">Компания</p>
                    <ul class="footer__nav-list" role="list">
                        <li><a href="/about/" class="footer__nav-link">О нас</a></li>
                        <li><a href="/delivery/" class="footer__nav-link">Доставка и оплата</a></li>
                        <li><a href="/contacts/" class="footer__nav-link">Контакты</a></li>
                        <li><a href="/reviews/" class="footer__nav-link">Отзывы</a></li>
                    </ul>
                </nav>

                <nav class="footer__nav-col" aria-label="Помощь">
                    <p class="footer__nav-title">Помощь</p>
                    <ul class="footer__nav-list" role="list">
                        <li><a href="/faq/" class="footer__nav-link">Вопросы и ответы</a></li>
                        <li><a href="/return/" class="footer__nav-link">Возврат и обмен</a></li>
                        <li><a href="/guarantee/" class="footer__nav-link">Гарантии</a></li>
                        <li><a href="/blog/" class="footer__nav-link">Блог</a></li>
                    </ul>
                </nav>

                <div class="footer__nav-col footer__contacts-col">
                    <p class="footer__nav-title">Контакты</p>
                    <address class="footer__contacts">
                        <a href="tel:+74951234567" class="footer__contact-link">
                            <!-- SVG: телефон -->
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 aria-hidden="true" focusable="false">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.13 11.91 19.79 19.79 0 0 1 1.06 3.22 2 2 0 0 1 3.05 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            +7 (495) 123-45-67
                        </a>
                        <a href="mailto:info@chokerz.ru" class="footer__contact-link">
                            <!-- SVG: email -->
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 aria-hidden="true" focusable="false">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                            info@chokerz.ru
                        </a>
                        <span class="footer__contact-addr">
                            <!-- SVG: метка -->
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 aria-hidden="true" focusable="false">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            г. Москва
                        </span>
                    </address>

                    <!-- Соцсети в колонке контактов -->
                    <div class="footer__social" aria-label="Социальные сети">
                        <a href="https://vk.com/chokerz"
                           class="footer__social-link"
                           target="_blank" rel="noopener noreferrer"
                           aria-label="ВКонтакте">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"
                                 aria-hidden="true" focusable="false">
                                <path d="M21.547 7h-3.29a.743.743 0 0 0-.655.392s-1.312 2.416-1.734 3.23C14.734 12.813 14 12.126 14 11.11V7.603A1.104 1.104 0 0 0 12.896 6.5h-2.474a1.982 1.982 0 0 0-1.75.813s1.255-.204 1.255 1.49c0 .42.022 1.626.04 2.64a.73.73 0 0 1-1.272.503 21.54 21.54 0 0 1-2.498-4.543.693.693 0 0 0-.63-.403h-2.99a.508.508 0 0 0-.48.685C3.005 10.175 6.918 18 11.38 18h1.878a.742.742 0 0 0 .742-.742v-1.23a.764.764 0 0 1 1.388-.43l1.018 1.869a1.089 1.089 0 0 0 .964.582h2.752a1.149 1.149 0 0 0 .782-1.928l-1.257-1.386c-.81-.892-.602-1.312.22-2.458l2.1-2.88A1.149 1.149 0 0 0 21.547 7z"/>
                            </svg>
                        </a>
                        <a href="https://t.me/chokerz"
                           class="footer__social-link"
                           target="_blank" rel="noopener noreferrer"
                           aria-label="Telegram">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"
                                 aria-hidden="true" focusable="false">
                                <path d="m20.665 3.717-17.73 6.837c-1.21.486-1.203 1.161-.222 1.462l4.552 1.42 10.532-6.645c.498-.303.953-.14.579.192L9.982 14.02l-.392 5.082c.574 0 .827-.264 1.148-.575l2.757-2.68 5.728 4.23c1.056.58 1.815.282 2.078-.98l3.762-17.718c.39-1.566-.597-2.274-1.398-1.662z"/>
                            </svg>
                        </a>
                    </div>
                </div>

            </div><!-- /footer__nav-grid -->

        </div><!-- /footer__top -->

        <!-- ── BOTTOM: копирайт + правовые ссылки ──────────────────────────── -->
        <div class="footer__bottom">

            <p class="footer__copyright">
                © <?= $currentYear ?> CHOKERZ. Все права защищены.
            </p>

            <nav class="footer__legal" aria-label="Правовые документы">
                <a href="/privacy/" class="footer__legal-link">Политика конфиденциальности</a>
                <a href="/terms/" class="footer__legal-link">Пользовательское соглашение</a>
                <a href="/sitemap.xml" class="footer__legal-link">Карта сайта</a>
            </nav>

        </div><!-- /footer__bottom -->

    </div><!-- /footer__container -->
</footer>

<?php
/*
 * ShowBody() — выводит JS подключённый через Asset::addJs() и другие ресурсы
 * ОБЯЗАТЕЛЕН в footer: без него defer-скрипты не подключатся
 */
$APPLICATION->ShowBody();
?>

</body>
</html>
