<?php

/**
 * PARTIAL: Karta receptu
 *
 * Očekává proměnnou:
 *   $recipe (RecipeDTO) – recept k zobrazení
 *
 * Volitelně:
 *   $isFavorite (bool) – zda je recept v oblíbených (pro zvýraznění srdíčka)
 */

$isFavorite ??= false;

?>
<article class="recipe-card">
    <a href="recept.php?slug=<?= htmlspecialchars($recipe->slug) ?>">
        <img
            class="recipe-card__image"
            src="<?= htmlspecialchars($recipe->image) ?>"
            alt="<?= htmlspecialchars($recipe->name) ?>"
        >
    </a>

    <div class="recipe-card__body">
        <span class="recipe-card__category">
            <?= htmlspecialchars($recipe->categoryName ?? '') ?>
        </span>

        <h2 class="recipe-card__name">
            <a href="recept.php?slug=<?= htmlspecialchars($recipe->slug) ?>">
                <?= htmlspecialchars($recipe->name) ?>
            </a>
        </h2>

        <div class="recipe-card__meta">
            <span class="recipe-card__time">
                &#9201; <?= htmlspecialchars($recipe->getFormattedTotalTime()) ?>
            </span>
            <span class="recipe-card__difficulty">
                <?= htmlspecialchars($recipe->difficultyName ?? '') ?>
            </span>
            <span class="recipe-card__servings">
                <?= $recipe->servings ?> porcí
            </span>
        </div>
    </div>

    <form method="post" class="recipe-card__form">
        <input type="hidden" name="recipe_id" value="<?= $recipe->id ?>">
        <button
            type="submit"
            name="toggle_favorite"
            class="recipe-card__btn<?= $isFavorite ? ' recipe-card__btn--active' : '' ?>"
        >
            <?= $isFavorite ? '&#9829; V oblíbených' : '&#9825; Přidat k oblíbeným' ?>
        </button>
    </form>
</article>
