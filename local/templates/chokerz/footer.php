<?php
/**
 * footer.php — подвал сайта CHOKERZ
 *
 * Структура по макету MAIN_PAGE.png (тёмный раздел снизу):
 *  - Бренд-колонка: SVG-лого + название + слоган
 *  - Колонка «Контакты»
 *  - Колонка «Информация»
 *  - Колонка «Покупателям»
 *  - Колонка «Следите» (соцсети)
 *  - Нижняя полоса: копирайт + юридические ссылки
 *
 * Здесь же закрываем <main> из header.php и подключаем JS-бандл
 * через <script type="module"> — модули автоматически defer.
 *
 * CSS — НЕ здесь.
 * Inline-скрипты — НЕ здесь.
 *
 * @package   CHOKERZ
 * @version   2.0
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

</main><!-- /.site-main (открыт в header.php) -->

<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer class="site-footer" aria-label="Подвал сайта">

    <!-- Основной блок футера (тёмный фон) -->
    <div class="site-footer__main">
        <div class="site-footer__container container">

            <!-- Бренд-колонка -->
            <div class="site-footer__brand footer-brand">
                <a href="/"
                   class="footer-brand__logo"
                   aria-label="CHOKERZ — на главную">
                    <img
                        src="<?= SITE_TEMPLATE_PATH ?>/images/logo.svg"
                        alt="CHOKERZ"
                        class="footer-brand__img"
                        width="80"
                        height="60"
                        loading="lazy"
                    >
                    <span class="footer-brand__name">CHOKERZ</span>
                </a>
                <p class="footer-brand__tagline">Амуниция для собак и кошек</p>

                <!-- Соцсети в бренд-колонке (мобиль) -->
                <ul class="footer-brand__social footer-social" role="list">
                    <li class="footer-social__item">
                        <a href="https://vk.com/chokerz"
                           class="footer-social__link footer-social__link--vk"
                           target="_blank"
                           rel="noopener noreferrer"
                           aria-label="ВКонтакте">
                            <svg class="footer-social__icon" width="20" height="20" viewBox="0 0 24 24"
                                 fill="currentColor" aria-hidden="true" focusable="false">
                                <path d="M15.07 2H8.93C3.33 2 2 3.33 2 8.93v6.14C2 20.67 3.33 22 8.93 22h6.14C20.67 22 22 20.67 22 15.07V8.93C22 3.33 20.67 2 15.07 2zm2.26 13.68h-1.63c-.62 0-.81-.49-1.92-1.61-1-.95-1.43-1.08-1.67-1.08-.34 0-.44.1-.44.57v1.47c0 .4-.13.64-1.18.64-1.74 0-3.67-1.06-5.02-3.03C3.84 10.04 3.4 8.2 3.4 7.8c0-.24.1-.47.57-.47h1.63c.42 0 .58.19.74.64.82 2.36 2.18 4.43 2.74 4.43.21 0 .31-.1.31-.64V9.7c-.07-1.15-.67-1.25-.67-1.66 0-.2.16-.41.42-.41h2.57c.35 0 .48.19.48.6v3.23c0 .35.15.48.26.48.21 0 .39-.13.78-.52 1.2-1.35 2.06-3.42 2.06-3.42.11-.24.31-.46.73-.46h1.63c.49 0 .6.25.49.6-.21.94-2.22 3.8-2.22 3.8-.17.28-.24.41 0 .72.17.24.74.74 1.12 1.19.69.78 1.22 1.44 1.37 1.9.12.45-.12.68-.59.68z"/>
                            </svg>
                        </a>
                    </li>
                    <li class="footer-social__item">
                        <a href="https://t.me/chokerz"
                           class="footer-social__link footer-social__link--tg"
                           target="_blank"
                           rel="noopener noreferrer"
                           aria-label="Telegram">
                            <svg class="footer-social__icon" width="20" height="20" viewBox="0 0 24 24"
                                 fill="currentColor" aria-hidden="true" focusable="false">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8l-1.68 7.92c-.12.56-.46.7-.93.43l-2.57-1.9-1.24 1.19c-.14.14-.26.26-.53.26l.19-2.64 4.84-4.37c.21-.19-.05-.29-.32-.1L7.86 14.32l-2.52-.79c-.55-.17-.56-.55.11-.81l9.85-3.8c.46-.17.86.11.7.88h.04z"/>
                            </svg>
                        </a>
                    </li>
                    <li class="footer-social__item">
                        <a href="https://www.instagram.com/chokerz/"
                           class="footer-social__link footer-social__link--ig"
                           target="_blank"
                           rel="noopener noreferrer"
                           aria-label="Instagram">
                            <svg class="footer-social__icon" width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 aria-hidden="true" focusable="false">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                            </svg>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Навигационные колонки -->
            <div class="site-footer__cols">

                <!-- Контакты -->
                <div class="site-footer__col footer-col">
                    <h3 class="footer-col__title">Контакты</h3>
                    <ul class="footer-col__list" role="list">
                        <li class="footer-col__item">
                            <a href="tel:+74951234567" class="footer-col__link">
                                +7 (495) 123-45-67
                            </a>
                        </li>
                        <li class="footer-col__item">
                            <a href="mailto:info@chokerz.ru" class="footer-col__link">
                                info@chokerz.ru
                            </a>
                        </li>
                        <li class="footer-col__item footer-col__item--address">
                            <address class="footer-col__address">
                                г. Москва, ул. Тестовая, д. 1
                            </address>
                        </li>
                    </ul>
                </div>

                <!-- Информация -->
                <nav class="site-footer__col footer-col" aria-label="Информация">
                    <h3 class="footer-col__title">Информация</h3>
                    <ul class="footer-col__list" role="list">
                        <li class="footer-col__item">
                            <a href="/about/" class="footer-col__link">О компании</a>
                        </li>
                        <li class="footer-col__item">
                            <a href="/delivery/" class="footer-col__link">Доставка и оплата</a>
                        </li>
                        <li class="footer-col__item">
                            <a href="/return/" class="footer-col__link">Возврат и обмен</a>
                        </li>
                        <li class="footer-col__item">
                            <a href="/guarantee/" class="footer-col__link">Гарантии</a>
                        </li>
                        <li class="footer-col__item">
                            <a href="/vacancies/" class="footer-col__link">Вакансии</a>
                        </li>
                    </ul>
                </nav>

                <!-- Покупателям -->
                <nav class="site-footer__col footer-col" aria-label="Покупателям">
                    <h3 class="footer-col__title">Покупателям</h3>
                    <ul class="footer-col__list" role="list">
                        <li class="footer-col__item">
                            <a href="/catalog/" class="footer-col__link">Каталог</a>
                        </li>
                        <li class="footer-col__item">
                            <a href="/faq/" class="footer-col__link">Вопросы и ответы</a>
                        </li>
                        <li class="footer-col__item">
                            <a href="/reviews/" class="footer-col__link">Отзывы</a>
                        </li>
                        <li class="footer-col__item">
                            <a href="/blog/" class="footer-col__link">Блог</a>
                        </li>
                        <li class="footer-col__item">
                            <a href="/contacts/" class="footer-col__link">Контакты</a>
                        </li>
                    </ul>
                </nav>

                <!-- Следите (соцсети с подписями) -->
                <div class="site-footer__col footer-col">
                    <h3 class="footer-col__title">Следите</h3>
                    <ul class="footer-col__list footer-col__list--social" role="list">
                        <li class="footer-col__item">
                            <a href="https://vk.com/chokerz"
                               class="footer-col__link footer-col__link--social"
                               target="_blank"
                               rel="noopener noreferrer">
                                <svg class="footer-col__social-icon" width="18" height="18" viewBox="0 0 24 24"
                                     fill="currentColor" aria-hidden="true" focusable="false">
                                    <path d="M15.07 2H8.93C3.33 2 2 3.33 2 8.93v6.14C2 20.67 3.33 22 8.93 22h6.14C20.67 22 22 20.67 22 15.07V8.93C22 3.33 20.67 2 15.07 2zm2.26 13.68h-1.63c-.62 0-.81-.49-1.92-1.61-1-.95-1.43-1.08-1.67-1.08-.34 0-.44.1-.44.57v1.47c0 .4-.13.64-1.18.64-1.74 0-3.67-1.06-5.02-3.03C3.84 10.04 3.4 8.2 3.4 7.8c0-.24.1-.47.57-.47h1.63c.42 0 .58.19.74.64.82 2.36 2.18 4.43 2.74 4.43.21 0 .31-.1.31-.64V9.7c-.07-1.15-.67-1.25-.67-1.66 0-.2.16-.41.42-.41h2.57c.35 0 .48.19.48.6v3.23c0 .35.15.48.26.48.21 0 .39-.13.78-.52 1.2-1.35 2.06-3.42 2.06-3.42.11-.24.31-.46.73-.46h1.63c.49 0 .6.25.49.6-.21.94-2.22 3.8-2.22 3.8-.17.28-.24.41 0 .72.17.24.74.74 1.12 1.19.69.78 1.22 1.44 1.37 1.9.12.45-.12.68-.59.68z"/>
                                </svg>
                                ВКонтакте
                            </a>
                        </li>
                        <li class="footer-col__item">
                            <a href="https://t.me/chokerz"
                               class="footer-col__link footer-col__link--social"
                               target="_blank"
                               rel="noopener noreferrer">
                                <svg class="footer-col__social-icon" width="18" height="18" viewBox="0 0 24 24"
                                     fill="currentColor" aria-hidden="true" focusable="false">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8l-1.68 7.92c-.12.56-.46.7-.93.43l-2.57-1.9-1.24 1.19c-.14.14-.26.26-.53.26l.19-2.64 4.84-4.37c.21-.19-.05-.29-.32-.1L7.86 14.32l-2.52-.79c-.55-.17-.56-.55.11-.81l9.85-3.8c.46-.17.86.11.7.88h.04z"/>
                                </svg>
                                Telegram
                            </a>
                        </li>
                        <li class="footer-col__item">
                            <a href="https://www.instagram.com/chokerz/"
                               class="footer-col__link footer-col__link--social"
                               target="_blank"
                               rel="noopener noreferrer">
                                <svg class="footer-col__social-icon" width="18" height="18" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round"
                                     aria-hidden="true" focusable="false">
                                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                                </svg>
                                Instagram
                            </a>
                        </li>
                        <li class="footer-col__item">
                            <a href="https://ozon.ru/seller/chokerz"
                               class="footer-col__link footer-col__link--social"
                               target="_blank"
                               rel="noopener noreferrer">
                                <svg class="footer-col__social-icon" width="18" height="18" viewBox="0 0 24 24"
                                     fill="currentColor" aria-hidden="true" focusable="false">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M12 7v5l3 3" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                Ozon
                            </a>
                        </li>
                        <li class="footer-col__item">
                            <a href="https://www.wildberries.ru/brands/chokerz"
                               class="footer-col__link footer-col__link--social"
                               target="_blank"
                               rel="noopener noreferrer">
                                <svg class="footer-col__social-icon" width="18" height="18" viewBox="0 0 24 24"
                                     fill="currentColor" aria-hidden="true" focusable="false">
                                    <rect x="2" y="2" width="20" height="20" rx="4"/>
                                    <path d="M8 12l2 2 4-4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Wildberries
                            </a>
                        </li>
                    </ul>
                </div>

            </div><!-- /.site-footer__cols -->

        </div><!-- /.site-footer__container -->
    </div><!-- /.site-footer__main -->

    <!-- Нижняя полоса (копирайт + юридические ссылки) -->
    <div class="site-footer__bottom footer-bottom">
        <div class="footer-bottom__container container">
            <p class="footer-bottom__copy">
                &copy; CHOKERZ, <?= date('Y') ?>
            </p>
            <nav class="footer-bottom__legal" aria-label="Юридические документы">
                <a href="/privacy/" class="footer-bottom__link">
                    Политика конфиденциальности
                </a>
                <a href="/terms/" class="footer-bottom__link">
                    Пользовательское соглашение
                </a>
                <a href="/requisites/" class="footer-bottom__link">
                    Реквизиты
                </a>
            </nav>
        </div>
    </div>

</footer><!-- /.site-footer -->

<?php
/**
 * ShowBody() — выводит JavaScript-файлы, подключённые через Asset::addJs() внутри компонентов.
 * Должен вызываться перед закрывающим </body>.
 */
$APPLICATION->ShowBody();
?>

<!--
     Главный JS-бандл подключается как ES-модуль.
     type="module" означает: автоматический defer + строгий режим.
     Ставим ПОСЛЕДНИМ перед </body> — все DOM-элементы уже распарсены.
-->
<script type="module" src="<?= SITE_TEMPLATE_PATH ?>/js/main.js"></script>

</body>
</html>
