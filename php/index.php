<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$recipeRepo   = new RecipeRepository();
$categoryRepo = new CategoryRepository();
$favorites    = new Favorites();

$featured   = $recipeRepo->getFeatured(6);
$categories = $categoryRepo->getAll();

$pageTitle      = 'Kottyho kuchařka';
$favoritesCount = $favorites->count();

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<section class="hero">
    <div class="hero-content">
        <h1>
            Objevte kouzlo<br>
            <span>domácí kuchyně</span>
        </h1>
        <p>Vítejte v naší online kuchařce! Najděte inspiraci mezi stovkami receptů pro každou příležitost.</p>
        <a href="recepty.php" class="btn-primary">Procházet recepty →</a>
    </div>
</section>

<form class="hero-search" action="vyhledavani.php" method="get">
    <div class="hero-search-box">
        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M21 21l-4.35-4.35m1.85-5.65a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <input type="text" name="q" placeholder="Co si dnes uvaříme?" aria-label="Vyhledávání receptů">
        <button type="submit">Hledat</button>
    </div>
</form>

<main>
    <section class="recommended-label">
        <h2>Doporučené recepty</h2>
        <p>Vyzkoušejte naše nejoblíbenější recepty</p>
    </section>

    <section class="recommended">
        <div class="recipes-grid">
            <?php foreach ($featured as $recipe): ?>
                <a href="recept.php?slug=<?= urlencode($recipe->slug) ?>">
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
        </div>
    </section>
</main>

<section class="recipe-categories">
    <div class="recipe-categories-inner">
        <h2>Kategorie receptů</h2>
        <div class="recipe-categories-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="kategorie.php?slug=<?= urlencode($cat->slug) ?>" class="recipe-category">
                    <img src="../<?= htmlspecialchars($cat->image) ?>" alt="<?= htmlspecialchars($cat->name) ?>">
                    <span><?= htmlspecialchars($cat->name) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="add-recipe-cta">
    <div class="add-recipe-inner">
        <div class="add-recipe-text">
            <h2>Přidej svůj vlastní recept</h2>
            <p>Máš oblíbený recept, který stojí za sdílení? Přidej ho a inspiruj ostatní.</p>
        </div>
        <a href="pridat-recept.php" class="add-recipe-button">Přidat recept →</a>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
