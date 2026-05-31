<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$recipeRepo = new RecipeRepository();
$favorites  = new Favorites();

// POST: toggle oblíbeného receptu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite'])) {
    csrf_verify();
    $recipeId = (int) $_POST['recipe_id'];
    if ($recipeRepo->getById($recipeId) !== null) {
        $favorites->toggle($recipeId);
    }
    session_write_close();
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

$recipes = $recipeRepo->getByIds($favorites->getIds());

$pageTitle      = 'Oblíbené recepty – Kottyho kuchařka';
$favoritesCount = $favorites->count();

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main>
    <section class="category-header">
        <h1>Oblíbené recepty</h1>
        <p>Tvoje nejoblíbenější kousky na jednom místě.</p>
    </section>

    <?php if ($recipes === []): ?>
        <section class="category-header">
            <p>Zatím nemáš žádné oblíbené recepty. <a href="recepty.php">Procházet recepty →</a></p>
        </section>
    <?php else: ?>
        <section class="recipes-grid">
            <?php foreach ($recipes as $recipe): ?>
                <?php $showRemove = true; require __DIR__ . '/partials/recipe-card.php'; ?>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>