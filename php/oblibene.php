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
                <article class="recipe-card">
                    <a href="recept.php?slug=<?= urlencode($recipe->slug) ?>">
                        <img src="../<?= htmlspecialchars($recipe->image) ?>" alt="<?= htmlspecialchars($recipe->name) ?>">
                    </a>
                    <div class="recipe-content">
                        <a href="recept.php?slug=<?= urlencode($recipe->slug) ?>" style="text-decoration:none;color:inherit;">
                            <h3><?= htmlspecialchars($recipe->name) ?></h3>
                            <p><?= htmlspecialchars($recipe->description) ?></p>
                        </a>
                        <div class="recipe-meta">
                            <span>⏱ <?= htmlspecialchars($recipe->getFormattedTotalTime()) ?></span>
                            <span>👥 <?= $recipe->servings ?> porcí</span>
                            <span>⭐ <?= htmlspecialchars($recipe->difficultyName ?? '') ?></span>
                        </div>
                        <form method="post" style="margin-top:16px;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="recipe_id" value="<?= $recipe->id ?>">
                            <button type="submit" name="toggle_favorite"
                                style="background:#e26a2c;color:#fff;border:none;padding:10px 24px;border-radius:50px;font-size:0.9rem;font-weight:600;cursor:pointer;width:100%;">
                                ♥ Odebrat z oblíbených
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>