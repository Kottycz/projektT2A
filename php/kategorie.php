<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$recipeRepo   = new RecipeRepository();
$categoryRepo = new CategoryRepository();
$favorites    = new Favorites();

$slug     = trim($_GET['slug'] ?? '');
$category = $slug !== '' ? $categoryRepo->getBySlug($slug) : null;

if ($category === null) {
    http_response_code(404);
    $pageTitle      = 'Kategorie nenalezena – Kottyho kuchařka';
    $favoritesCount = $favorites->count();
    require __DIR__ . '/partials/header.php';
    echo '<main class="category-header"><h1>Kategorie nenalezena</h1><p><a href="kategorie-receptu.php">Zpět na kategorie →</a></p></main>';
    require __DIR__ . '/partials/footer.php';
    exit;
}

$recipes = $recipeRepo->getByCategorySlug($slug);

$pageTitle      = htmlspecialchars($category->name) . ' – Kottyho kuchařka';
$favoritesCount = $favorites->count();

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main>
    <section class="category-header">
        <h1><?= htmlspecialchars($category->name) ?></h1>
        <?php if ($category->description !== ''): ?>
            <p><?= htmlspecialchars($category->description) ?></p>
        <?php endif; ?>
    </section>

    <section class="recipes-grid">
        <?php foreach ($recipes as $recipe): ?>
            <?php require __DIR__ . '/partials/recipe-card.php'; ?>
        <?php endforeach; ?>

        <?php if ($recipes === []): ?>
            <p>V této kategorii zatím nejsou žádné recepty.</p>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
