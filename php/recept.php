<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$recipeRepo = new RecipeRepository();
$favorites  = new Favorites();

// POST: toggle oblíbeného receptu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite'])) {
    csrf_verify();
    $recipeId = (int) $_POST['recipe_id'];
    $recipe   = $recipeRepo->getById($recipeId);

    if ($recipe !== null) {
        $wasAdded = !$favorites->contains($recipe->id);
        $favorites->toggle($recipe->id);
        session_write_close();

        if ($wasAdded) {
            header('Location: oblibene-potvrzeni.php');
        } else {
            header('Location: ' . $_SERVER['REQUEST_URI']);
        }
    } else {
        session_write_close();
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
$galleryImages  = $recipeRepo->getImages($recipe->id);
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
                <img src="/<?= htmlspecialchars($recipe->image) ?>" alt="<?= htmlspecialchars($recipe->name) ?>">
            </div>

            <?php if ($galleryImages !== []): ?>
            <div class="recipe-gallery">
                <?php foreach ($galleryImages as $img): ?>
                    <div class="recipe-gallery__item">
                        <img src="/<?= htmlspecialchars($img->image) ?>" alt="<?= htmlspecialchars($recipe->name) ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
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
            <p class="recipe-description"><?= htmlspecialchars($recipe->description) ?></p>
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
            <form method="post" class="recipe-actions__form">
                <?= csrf_field() ?>
                <input type="hidden" name="recipe_id" value="<?= $recipe->id ?>">
                <button type="submit" name="toggle_favorite"
                    class="btn-action <?= $isFavorite ? 'btn-action--gray' : 'btn-action--orange' ?>">
                    <?= $isFavorite ? '♥ Odebrat z oblíbených' : '♡ Přidat do oblíbených' ?>
                </button>
            </form>
            <a href="upravit-recept.php?slug=<?= urlencode($recipe->slug) ?>"
               class="btn-action btn-action--dark">
                ✏ Upravit recept
            </a>
            <a href="smazat-recept.php?slug=<?= urlencode($recipe->slug) ?>"
               class="btn-action btn-action--red">
                🗑 Smazat recept
            </a>
            <a href="recepty.php" class="btn-secondary">← Zpět na recepty</a>
        </div>

    </article>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
