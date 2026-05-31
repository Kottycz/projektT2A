<?php

/**
 * PARTIAL: Karta receptu
 *
 * Očekává proměnnou:
 *   $recipe (RecipeDTO) – recept k zobrazení
 *
 * Volitelně:
 *   $showRemove (bool) – zobrazit tlačítko „Odebrat z oblíbených" (default false)
 */

$showRemove ??= false;

?>
<?php if (!$showRemove): ?>
<a href="recept.php?slug=<?= htmlspecialchars($recipe->slug) ?>" class="recipe-card-link">
<?php endif; ?>

<article class="recipe-card">
    <?php if ($showRemove): ?>
    <a href="recept.php?slug=<?= htmlspecialchars($recipe->slug) ?>">
    <?php endif; ?>
    <img src="/<?= htmlspecialchars($recipe->image) ?>" alt="<?= htmlspecialchars($recipe->name) ?>">
    <?php if ($showRemove): ?>
    </a>
    <?php endif; ?>

    <div class="recipe-content">
        <?php if ($showRemove): ?>
        <a href="recept.php?slug=<?= htmlspecialchars($recipe->slug) ?>" class="recipe-card__title-link">
        <?php endif; ?>
        <h3><?= htmlspecialchars($recipe->name) ?></h3>
        <p><?= htmlspecialchars($recipe->description) ?></p>
        <?php if ($showRemove): ?></a><?php endif; ?>

        <div class="recipe-meta">
            <span>⏱ <?= htmlspecialchars($recipe->getFormattedTotalTime()) ?></span>
            <span>👥 <?= $recipe->servings ?> porcí</span>
            <span>⭐ <?= htmlspecialchars($recipe->difficultyName ?? '') ?></span>
        </div>

        <?php if ($showRemove): ?>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="recipe_id" value="<?= $recipe->id ?>">
            <button type="submit" name="toggle_favorite" class="btn-remove-fav">
                ♥ Odebrat z oblíbených
            </button>
        </form>
        <?php endif; ?>
    </div>
</article>

<?php if (!$showRemove): ?>
</a>
<?php endif; ?>
