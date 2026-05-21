<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$recipeRepo = new RecipeRepository();
$favorites  = new Favorites();

$recipes = $recipeRepo->getAll();

$pageTitle      = 'Recepty – Kottyho kuchařka';
$favoritesCount = $favorites->count();

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main>
    <section class="category-header">
        <h1>Všechny recepty</h1>
        <p>Najděte si svůj oblíbený pokrm.</p>
    </section>

    <section class="recipes-grid">
        <?php foreach ($recipes as $recipe): ?>
            <a href="recept.php?slug=<?= urlencode($recipe->slug) ?>" class="recipe-card-link">
                <article class="recipe-card">
                    <img src="../<?= htmlspecialchars($recipe->image) ?>" alt="<?= htmlspecialchars($recipe->name) ?>">
                    <div class="recipe-content">
                        <h3><?= htmlspecialchars($recipe->name) ?></h3>
                        <p><?= htmlspecialchars($recipe->description) ?></p>
                        <div class="recipe-meta">
                            <span>⏱ <?= htmlspecialchars($recipe->getFormattedTotalTime()) ?></span>
                            <span>👥 <?= $recipe->servings ?> porcí</span>
                            <span>⭐ <?= htmlspecialchars($recipe->difficultyName ?? '') ?></span>
                        </div>
                    </div>
                </article>
            </a>
        <?php endforeach; ?>

        <?php if ($recipes === []): ?>
            <p>Zatím tu nejsou žádné recepty.</p>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
