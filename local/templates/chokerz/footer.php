<?php
/**
 * Footer шаблона сайта CHOKERZ
 * Подвал сайта с информацией и ссылками
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

</main>

<footer class="footer">
    <div class="footer__container container">
        <div class="footer__top">
            <div class="footer__brand">
                <a href="/" class="footer__logo">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/images/logo.svg" alt="CHOKERZ" class="footer__logo-img">
                    <span class="footer__logo-text">CHOKERZ</span>
                </a>
                <p class="footer__desc">Амуниция для ваших питомцев — качество и стиль</p>
            </div>
            
            <div class="footer__nav">
                <div class="footer__nav-col">
                    <h3 class="footer__nav-title">Каталог</h3>
                    <ul class="footer__nav-list">
                        <li><a href="/catalog/ozon/">На Озоне</a></li>
                        <li><a href="/catalog/wildberries/">На ВБ</a></li>
                        <li><a href="/catalog/yandex-market/">На Яндекс.Маркете</a></li>
                        <li><a href="/catalog/wholesale/">Оптовым покупателям</a></li>
                    </ul>
                </div>
                
                <div class="footer__nav-col">
                    <h3 class="footer__nav-title">О нас</h3>
                    <ul class="footer__nav-list">
                        <li><a href="/about/">О компании</a></li>
                        <li><a href="/delivery/">Доставка и оплата</a></li>
                        <li><a href="/contacts/">Контакты</a></li>
                        <li><a href="/vacancies/">Вакансии</a></li>
                    </ul>
                </div>
                
                <div class="footer__nav-col">
                    <h3 class="footer__nav-title">Помощь</h3>
                    <ul class="footer__nav-list">
                        <li><a href="/faq/">Вопросы и ответы</a></li>
                        <li><a href="/return/">Возврат и обмен</a></li>
                        <li><a href="/guarantee/">Гарантии</a></li>
                        <li><a href="/reviews/">Отзывы</a></li>
                    </ul>
                </div>
                
                <div class="footer__nav-col">
                    <h3 class="footer__nav-title">Блог</h3>
                    <ul class="footer__nav-list">
                        <li><a href="/blog/">Статьи</a></li>
                        <li><a href="/blog/videos/">Видео</a></li>
                        <li><a href="/blog/advices/">Советы</a></li>
                        <li><a href="/blog/news/">Новости</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer__contacts">
                <h3 class="footer__nav-title">Контакты</h3>
                <div class="footer__contact-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    <span>+7 (495) 123-45-67</span>
                </div>
                <div class="footer__contact-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <span>info@chokerz.ru</span>
                </div>
                <div class="footer__contact-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span>г. Москва, ул. Тестовая, 1</span>
                </div>
            </div>
        </div>
        
        <div class="footer__bottom">
            <div class="footer__copyright">
                <p>© 2026 CHOKERZ — амуниция для животных. Все права защищены.</p>
            </div>
            
            <div class="footer__social">
                <a href="https://vk.com/chokerz" class="footer__social-link" target="_blank" rel="noopener noreferrer">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 3a10 10 0 0 1 0 20H5A10 10 0 0 1 5 3h14m0 2a8 8 0 0 0 0 16H5a8 8 0 0 0 0-16h14m-2 9a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"></path>
                    </svg>
                </a>
                <a href="https://t.me/chokerz" class="footer__social-link" target="_blank" rel="noopener noreferrer">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"></path>
                    </svg>
                </a>
                <a href="https://www.instagram.com/chokerz/" class="footer__social-link" target="_blank" rel="noopener noreferrer">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5" stroke="currentColor" stroke-width="2" fill="none"></rect>
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" stroke="currentColor" stroke-width="2"></line>
                    </svg>
                </a>
            </div>
            
            <div class="footer__legal">
                <a href="/privacy/">Политика конфиденциальности</a>
                <a href="/terms/">Пользовательское соглашение</a>
            </div>
        </div>
    </div>
</footer>

<?php $APPLICATION->ShowBody() ?>

</body>
</html>
