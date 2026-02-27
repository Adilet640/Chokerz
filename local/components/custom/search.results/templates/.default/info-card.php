<?php
/**
 * Карточка информационного материала в результатах поиска CHOKERZ
 * Переменная $infoItem обязательна в области видимости
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $infoItem */
$typeLabel = match ($infoItem['TYPE']) {
    'blog'  => 'Статья',
    'page'  => 'Страница',
    default => 'Материал',
};
?>
<div class="search-info-card search-info-card--<?= htmlspecialcharsEx($infoItem['TYPE']) ?>">

    <div class="search-info-card__head">
        <span class="search-info-card__type"><?= $typeLabel ?></span>
        <?php if (!empty($infoItem['DATE'])): ?>
        <time class="search-info-card__date"><?= htmlspecialcharsEx($infoItem['DATE']) ?></time>
        <?php endif; ?>
    </div>

    <h3 class="search-info-card__title">
        <a href="<?= htmlspecialcharsEx($infoItem['URL']) ?>" class="search-info-card__link">
            <?= htmlspecialcharsEx($infoItem['TITLE']) ?>
        </a>
    </h3>

    <?php if (!empty($infoItem['BODY'])): ?>
    <p class="search-info-card__text"><?= htmlspecialcharsEx($infoItem['BODY']) ?></p>
    <?php endif; ?>

</div>
