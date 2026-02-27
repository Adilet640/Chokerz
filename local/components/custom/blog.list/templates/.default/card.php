<?php
/**
 * Карточка материала блога — подключается из template.php
 * Переменная $item обязательна в области видимости
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $item */
?>
<li class="blog-grid__item blog-card<?= $item['IS_VIDEO'] ? ' blog-card--video' : '' ?>" data-blog-card>

    <a href="<?= htmlspecialcharsEx($item['DETAIL_URL']) ?>" class="blog-card__img-wrap" tabindex="-1" aria-hidden="true">

        <?php if (!empty($item['PREVIEW_SRC'])): ?>
        <img
            src="<?= htmlspecialcharsEx($item['PREVIEW_SRC']) ?>"
            alt="<?= htmlspecialcharsEx($item['NAME']) ?>"
            class="blog-card__img"
            loading="lazy"
            width="420"
            height="280"
        >
        <?php else: ?>
        <div class="blog-card__img-placeholder" aria-hidden="true"></div>
        <?php endif; ?>

        <!-- Бейдж типа материала -->
        <?php if ($item['IS_VIDEO']): ?>
        <span class="blog-card__badge blog-card__badge--video" aria-hidden="true">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <polygon points="5 3 19 12 5 21 5 3"/>
            </svg>
            Видео
            <?php if (!empty($item['VIDEO_DURATION'])): ?>
            <span class="blog-card__duration"><?= htmlspecialcharsEx($item['VIDEO_DURATION']) ?></span>
            <?php endif; ?>
        </span>
        <?php else: ?>
        <span class="blog-card__badge blog-card__badge--article" aria-hidden="true">Статья</span>
        <?php endif; ?>

    </a>

    <div class="blog-card__body">

        <?php if (!empty($item['DATE'])): ?>
        <time class="blog-card__date" datetime="<?= htmlspecialcharsEx($item['DATE']) ?>"><?= htmlspecialcharsEx($item['DATE']) ?></time>
        <?php endif; ?>

        <h2 class="blog-card__title">
            <a href="<?= htmlspecialcharsEx($item['DETAIL_URL']) ?>" class="blog-card__title-link">
                <?= htmlspecialcharsEx($item['NAME']) ?>
            </a>
        </h2>

        <?php if (!empty($item['PREVIEW_TEXT'])): ?>
        <p class="blog-card__text"><?= htmlspecialcharsEx($item['PREVIEW_TEXT']) ?></p>
        <?php endif; ?>

        <?php if (!empty($item['TAGS'])): ?>
        <ul class="blog-card__tags blog-tags" aria-label="Теги">
            <?php foreach (array_slice($item['TAGS'], 0, 3) as $tag): ?>
            <li class="blog-tags__item">
                <a
                    href="<?= $buildUrl(['tag' => $tag]) ?>"
                    class="blog-tags__link"
                ><?= htmlspecialcharsEx($tag) ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

    </div><!-- /blog-card__body -->

</li>
