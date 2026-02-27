<?php
/**
 * Шаблон детальной страницы блога CHOKERZ
 * Layout: контент (80%) + сайдбар (20%)
 * Сайдбар: оглавление, навигация по разделу, теги, «Смотрите также»
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */
$item    = $arResult['ITEM']    ?? [];
$related = $arResult['RELATED'] ?? [];

if (empty($item)) {
    return;
}
?>

<article class="blog-detail" itemscope itemtype="https://schema.org/Article">

    <!-- ХЛЕБНЫЕ КРОШКИ генерируются стандартным компонентом на странице -->

    <!-- EYEBROW -->
    <div class="blog-detail__eyebrow">
        <a href="/blog/" class="blog-detail__back-link">БЛОГ</a>
    </div>

    <!-- ЗАГОЛОВОК -->
    <header class="blog-detail__header">
        <h1 class="blog-detail__title" itemprop="headline"><?= htmlspecialcharsEx($item['NAME']) ?></h1>

        <div class="blog-detail__meta">
            <?php if ($item['DATE']): ?>
            <time class="blog-detail__date" datetime="<?= htmlspecialcharsEx($item['DATE']) ?>" itemprop="datePublished">
                <?= htmlspecialcharsEx($item['DATE']) ?>
            </time>
            <?php endif; ?>

            <?php if ($item['IS_VIDEO']): ?>
            <span class="blog-detail__type blog-detail__type--video">Видео</span>
            <?php else: ?>
            <span class="blog-detail__type blog-detail__type--article">Статья</span>
            <?php endif; ?>
        </div>
    </header>

    <!-- ГЕРОИZON-ИЗОБРАЖЕНИЕ / ВИДЕО -->
    <?php if ($item['IS_VIDEO'] && !empty($item['VIDEO_EMBED'])): ?>
    <div class="blog-detail__video-wrap">
        <iframe
            class="blog-detail__video"
            src="<?= htmlspecialcharsEx($item['VIDEO_EMBED']) ?>"
            allowfullscreen
            loading="lazy"
            title="<?= htmlspecialcharsEx($item['NAME']) ?>"
        ></iframe>
    </div>
    <?php elseif (!empty($item['HERO_SRC'])): ?>
    <div class="blog-detail__hero">
        <img
            src="<?= htmlspecialcharsEx($item['HERO_SRC']) ?>"
            alt="<?= htmlspecialcharsEx($item['NAME']) ?>"
            class="blog-detail__hero-img"
            itemprop="image"
            width="860"
            height="480"
            loading="eager"
        >
    </div>
    <?php endif; ?>

    <!-- ОСНОВНОЙ МАКЕТ: контент + сайдбар -->
    <div class="blog-detail__layout">

        <!-- ТЕЛО СТАТЬИ -->
        <div class="blog-detail__body" itemprop="articleBody" data-blog-body>
            <?= $item['DETAIL_TEXT'] ?>
        </div>

        <!-- САЙДБАР -->
        <aside class="blog-detail__sidebar blog-sidebar" aria-label="Навигация по статье">

            <!-- Оглавление -->
            <?php if (!empty($item['TOC'])): ?>
            <div class="blog-sidebar__section">
                <h2 class="blog-sidebar__title">Содержание</h2>
                <nav class="blog-sidebar__toc blog-toc" aria-label="Содержание статьи" data-blog-toc>
                    <ol class="blog-toc__list">
                        <?php foreach ($item['TOC'] as $tocItem): ?>
                        <li class="blog-toc__item">
                            <a
                                href="#<?= htmlspecialcharsEx($tocItem['ANCHOR']) ?>"
                                class="blog-toc__link"
                                data-toc-anchor="<?= htmlspecialcharsEx($tocItem['ANCHOR']) ?>"
                            ><?= htmlspecialcharsEx($tocItem['TEXT']) ?></a>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            </div>
            <?php endif; ?>

            <!-- Навигация по разделу -->
            <div class="blog-sidebar__section">
                <h2 class="blog-sidebar__title">Навигация</h2>
                <nav class="blog-sidebar__nav" aria-label="Разделы блога">
                    <ul class="blog-sidebar__nav-list">
                        <li><a href="/blog/" class="blog-sidebar__nav-link">Все материалы</a></li>
                        <li><a href="/blog/?type=article" class="blog-sidebar__nav-link">Статьи</a></li>
                        <li><a href="/blog/?type=video" class="blog-sidebar__nav-link">Видео</a></li>
                    </ul>
                </nav>
            </div>

            <!-- Теги -->
            <?php if (!empty($item['TAGS'])): ?>
            <div class="blog-sidebar__section">
                <h2 class="blog-sidebar__title">Теги</h2>
                <ul class="blog-sidebar__tags blog-tags" aria-label="Теги статьи">
                    <?php foreach ($item['TAGS'] as $tag): ?>
                    <li class="blog-tags__item">
                        <a href="/blog/?tag=<?= urlencode($tag) ?>" class="blog-tags__link">
                            <?= htmlspecialcharsEx($tag) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Смотрите также -->
            <?php if (!empty($related)): ?>
            <div class="blog-sidebar__section">
                <h2 class="blog-sidebar__title">Смотрите также</h2>
                <ul class="blog-sidebar__related blog-related" aria-label="Похожие материалы">
                    <?php foreach ($related as $rel): ?>
                    <li class="blog-related__item">
                        <a href="<?= htmlspecialcharsEx($rel['DETAIL_URL']) ?>" class="blog-related__link">
                            <?php if (!empty($rel['PREVIEW_SRC'])): ?>
                            <img
                                src="<?= htmlspecialcharsEx($rel['PREVIEW_SRC']) ?>"
                                alt="<?= htmlspecialcharsEx($rel['NAME']) ?>"
                                class="blog-related__img"
                                loading="lazy"
                                width="60"
                                height="60"
                            >
                            <?php endif; ?>
                            <span class="blog-related__name"><?= htmlspecialcharsEx($rel['NAME']) ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

        </aside><!-- /blog-detail__sidebar -->

    </div><!-- /blog-detail__layout -->

    <!-- НИЖНИЕ ТЕГИ -->
    <?php if (!empty($item['TAGS'])): ?>
    <footer class="blog-detail__footer">
        <div class="blog-detail__tags-label">Теги:</div>
        <ul class="blog-detail__tags blog-tags" aria-label="Теги">
            <?php foreach ($item['TAGS'] as $tag): ?>
            <li class="blog-tags__item">
                <a href="/blog/?tag=<?= urlencode($tag) ?>" class="blog-tags__link">
                    <?= htmlspecialcharsEx($tag) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </footer>
    <?php endif; ?>

</article>

<!-- СВЯЗАННЫЕ МАТЕРИАЛЫ (мобильная версия — под статьёй) -->
<?php if (!empty($related)): ?>
<section class="blog-detail__related-mobile blog-related-section" aria-label="Смотрите также">
    <h2 class="blog-related-section__title">Смотрите также</h2>
    <ul class="blog-related-section__list">
        <?php foreach ($related as $rel): ?>
        <li class="blog-related-section__item">
            <a href="<?= htmlspecialcharsEx($rel['DETAIL_URL']) ?>" class="blog-related-section__link">
                <?php if (!empty($rel['PREVIEW_SRC'])): ?>
                <img
                    src="<?= htmlspecialcharsEx($rel['PREVIEW_SRC']) ?>"
                    alt="<?= htmlspecialcharsEx($rel['NAME']) ?>"
                    class="blog-related-section__img"
                    loading="lazy"
                    width="80"
                    height="80"
                >
                <?php endif; ?>
                <span class="blog-related-section__name"><?= htmlspecialcharsEx($rel['NAME']) ?></span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>
