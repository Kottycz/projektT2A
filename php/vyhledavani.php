<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$recipeRepo = new RecipeRepository();
$favorites  = new Favorites();

$query   = trim($_GET['q'] ?? '');
$recipes = $query !== '' ? $recipeRepo->search($query) : [];

$pageTitle      = 'Vyhledávání – Kottyho kuchařka';
$favoritesCount = $favorites->count();

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<form class="hero-search" action="vyhledavani.php" method="get">
    <div class="hero-search-box">
        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M21 21l-4.35-4.35m1.85-5.65a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Co si dnes uvaříme?" aria-label="Vyhledávání receptů">
        <button type="submit">Hledat</button>
    </div>
</form>

<main class="search-results-page">
    <?php if ($query === ''): ?>
        <h2>Zadejte hledaný výraz</h2>

    <?php elseif ($recipes === []): ?>
        <h2>Výsledky pro: <?= htmlspecialchars($query) ?></h2>
        <p class="search-subtitle">Bohužel jsme nic nenašli. Zkuste jiný výraz.</p>

    <?php else: ?>
        <h2>Výsledky pro: <?= htmlspecialchars($query) ?></h2>
        <p class="search-subtitle">
            Našli jsme <?= count($recipes) ?>
            <?= count($recipes) === 1 ? 'recept' : (count($recipes) < 5 ? 'recepty' : 'receptů') ?>.
        </p>

        <section class="recipes-grid">
            <?php foreach ($recipes as $recipe): ?>
                <a href="recept.php?slug=<?= urlencode($recipe->slug) ?>" class="recipe-card-link">
                    <article class="recipe-card">
                        <img src="/<?= htmlspecialchars($recipe->image) ?>" alt="<?= htmlspecialchars($recipe->name) ?>">
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
        </section>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
