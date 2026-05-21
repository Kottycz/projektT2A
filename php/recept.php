<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$recipeRepo = new RecipeRepository();
$favorites  = new Favorites();

// POST: toggle oblíbeného receptu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite'])) {
    $recipeId = (int) $_POST['recipe_id'];
    $recipe   = $recipeRepo->getById($recipeId);

    if ($recipe !== null) {
        $wasAdded = !$favorites->contains($recipe->id);
        $favorites->toggle($recipe->id);

        if ($wasAdded) {
            header('Location: oblibene-potvrzeni.php');
        } else {
            header('Location: ' . $_SERVER['REQUEST_URI']);
        }
    } else {
        header('Location: ' . $_SERVER['REQUEST_URI']);
    }
    exit;
}

// Načtení receptu podle slugu
$slug   = trim($_GET['slug'] ?? '');
$recipe = $slug !== '' ? $recipeRepo->getBySlug($slug) : null;

if ($recipe === null) {
    http_response_code(404);
    $pageTitle      = 'Recept nenalezen – Kottyho kuchařka';
    $favoritesCount = $favorites->count();
    require __DIR__ . '/partials/header.php';
    echo '<main><section class="category-header"><h1>Recept nenalezen</h1><p><a href="recepty.php">Zpět na recepty →</a></p></section></main>';
    require __DIR__ . '/partials/footer.php';
    exit;
}

$ingredients    = $recipeRepo->getIngredients($recipe->id);
$steps          = $recipeRepo->getSteps($recipe->id);
$isFavorite     = $favorites->contains($recipe->id);
$pageTitle      = $recipe->name . ' – Kottyho kuchařka';
$favoritesCount = $favorites->count();

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main class="recipe-page">
    <article class="recipe-container">

        <header class="recipe-header">
            <h1><?= htmlspecialchars($recipe->name) ?></h1>
            <div class="recipe-image-wrapper">
                <img src="../<?= htmlspecialchars($recipe->image) ?>" alt="<?= htmlspecialchars($recipe->name) ?>">
            </div>
        </header>

        <div class="recipe-meta-strip">
            <div class="meta-item">
                <span>⏱</span>
                <p><?= htmlspecialchars($recipe->getFormattedTotalTime()) ?></p>
            </div>
            <div class="meta-item">
                <span>👥</span>
                <p><?= $recipe->servings ?> porcí</p>
            </div>
            <div class="meta-item">
                <span>⭐</span>
                <p><?= htmlspecialchars($recipe->difficultyName ?? '') ?></p>
            </div>
            <div class="meta-item">
                <span>🍽</span>
                <p><?= htmlspecialchars($recipe->categoryName ?? '') ?></p>
            </div>
        </div>

        <?php if ($recipe->description !== ''): ?>
            <p style="text-align:center; color:#555; font-size:1.05rem; max-width:700px; margin: 0 auto 40px auto; line-height:1.7;">
                <?= htmlspecialchars($recipe->description) ?>
            </p>
        <?php endif; ?>

        <div class="recipe-content-grid">
            <section class="ingredients">
                <h2>Ingredience</h2>
                <?php if ($ingredients === []): ?>
                    <p>Recept nemá zadané suroviny.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($ingredients as $ing): ?>
                            <li>
                                <?php if ($ing->getFormattedAmount() !== ''): ?>
                                    <strong><?= htmlspecialchars($ing->getFormattedAmount()) ?></strong>
                                <?php endif; ?>
                                <?= htmlspecialchars($ing->name) ?>
                                <?php if ($ing->note !== ''): ?>
                                    <em>(<?= htmlspecialchars($ing->note) ?>)</em>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <section class="instructions">
                <h2>Postup přípravy</h2>
                <?php if ($steps === []): ?>
                    <p>Recept zatím nemá zadaný postup.</p>
                <?php else: ?>
                    <ol>
                        <?php foreach ($steps as $step): ?>
                            <li><?= htmlspecialchars($step->description) ?></li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </section>
        </div>

        <div class="recipe-actions">
            <form method="post" style="display:inline;">
                <input type="hidden" name="recipe_id" value="<?= $recipe->id ?>">
                <button type="submit" name="toggle_favorite"
                    style="background:<?= $isFavorite ? '#888' : '#e26a2c' ?>;color:#fff;border:none;padding:16px 35px;border-radius:50px;font-size:1rem;font-weight:600;cursor:pointer;transition:background 0.3s;">
                    <?= $isFavorite ? '♥ Odebrat z oblíbených' : '♡ Přidat do oblíbených' ?>
                </button>
            </form>
            <a href="upravit-recept.php?slug=<?= urlencode($recipe->slug) ?>"
               style="background:#fff;color:#e26a2c;border:2px solid #e26a2c;padding:14px 35px;border-radius:50px;font-size:1rem;font-weight:600;cursor:pointer;text-decoration:none;transition:background 0.3s;">
                ✏ Upravit recept
            </a>
            <a href="recepty.php" class="btn-secondary">← Zpět na recepty</a>
        </div>

    </article>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
