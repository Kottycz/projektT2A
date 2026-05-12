<?php

declare(strict_types=1);

/**
 * UKÁZKOVÁ STRÁNKA – detail receptu s ingrediencemi, postupem a galerií
 *
 * Co tato stránka ukazuje:
 *   - Načtení receptu podle slugu z URL (?slug=...)
 *   - Výpis ingrediencí s formátovaným množstvím a jednotkou
 *   - Výpis kroků postupu seřazených podle step_number
 *   - Galerie dalších obrázků
 *   - Přidání/odebrání z oblíbených (Post/Redirect/Get)
 *   - Ošetření stavu, kdy recept neexistuje (404)
 */

// 1) Načteme všechny třídy
require_once __DIR__ . '/src/bootstrap.php';

// 2) Vytvoříme instance
$recipeRepo = new RecipeRepository();
$favorites = new Favorites();

// 3) Zpracování akce "přepnout oblíbený"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite'])) {
	$recipeId = (int) $_POST['recipe_id'];
	$recipe = $recipeRepo->getById($recipeId);

	if ($recipe !== null) {
		$favorites->toggle($recipe->id);
	}

	header('Location: ' . $_SERVER['REQUEST_URI']);
	exit;
}

// 4) Načtení receptu podle slugu z URL
$slug = trim($_GET['slug'] ?? '');
$recipe = $slug !== '' ? $recipeRepo->getBySlug($slug) : null;

if ($recipe === null) {
	http_response_code(404);
	$pageTitle = 'Recept nenalezen';
	$favoritesCount = $favorites->count();
	require __DIR__ . '/partials/header.php';
	echo '<main class="container"><h1>Recept nenalezen</h1><p>Zkuste se vrátit na <a href="index.php">hlavní stránku</a>.</p></main>';
	require __DIR__ . '/partials/footer.php';
	exit;
}

// 5) Načtení souvisejících dat
$images = $recipeRepo->getImages($recipe->id);
$ingredients = $recipeRepo->getIngredients($recipe->id);
$steps = $recipeRepo->getSteps($recipe->id);

// 6) Proměnné pro header
$pageTitle = $recipe->name . ' – Kottyho kuchařka';
$favoritesCount = $favorites->count();
$isFavorite = $favorites->contains($recipe->id);

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main class="container">
    <article class="recipe-detail">
        <!-- Levá strana – obrázky -->
        <div>
            <img
                class="recipe-detail__image"
                src="<?= htmlspecialchars($recipe->image) ?>"
                alt="<?= htmlspecialchars($recipe->name) ?>"
            >

            <?php if ($images !== []): ?>
                <div class="recipe-detail__gallery">
                    <?php foreach ($images as $img): ?>
                        <img
                            src="<?= htmlspecialchars($img->image) ?>"
                            alt="<?= htmlspecialchars($recipe->name) ?>"
                        >
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pravá strana – info -->
        <div>
            <span class="recipe-detail__category">
                <?= htmlspecialchars($recipe->categoryName ?? '') ?>
            </span>

            <h1 class="recipe-detail__name">
                <?= htmlspecialchars($recipe->name) ?>
            </h1>

            <p class="recipe-detail__description">
                <?= htmlspecialchars($recipe->description) ?>
            </p>

            <dl class="recipe-detail__meta">
                <div>
                    <dt>Příprava</dt>
                    <dd><?= $recipe->prepTimeMinutes ?> min</dd>
                </div>
                <div>
                    <dt>Vaření</dt>
                    <dd><?= $recipe->cookTimeMinutes ?> min</dd>
                </div>
                <div>
                    <dt>Celkem</dt>
                    <dd><?= htmlspecialchars($recipe->getFormattedTotalTime()) ?></dd>
                </div>
                <div>
                    <dt>Porcí</dt>
                    <dd><?= $recipe->servings ?></dd>
                </div>
                <div>
                    <dt>Obtížnost</dt>
                    <dd><?= htmlspecialchars($recipe->difficultyName ?? '') ?></dd>
                </div>
            </dl>

            <form method="post">
                <input type="hidden" name="recipe_id" value="<?= $recipe->id ?>">
                <button
                    type="submit"
                    name="toggle_favorite"
                    class="recipe-detail__btn<?= $isFavorite ? ' recipe-detail__btn--active' : '' ?>"
                >
                    <?= $isFavorite ? '&#9829; Odebrat z oblíbených' : '&#9825; Přidat k oblíbeným' ?>
                </button>
            </form>
        </div>
    </article>

    <!-- Ingredience -->
    <section class="recipe-ingredients">
        <h2>Suroviny</h2>
        <?php if ($ingredients === []): ?>
            <p>Recept nemá zadané suroviny.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($ingredients as $ing): ?>
                    <li>
                        <span class="recipe-ingredients__amount">
                            <?= htmlspecialchars($ing->getFormattedAmount()) ?>
                        </span>
                        <span class="recipe-ingredients__name">
                            <?= htmlspecialchars($ing->name) ?>
                        </span>
                        <?php if ($ing->note !== ''): ?>
                            <span class="recipe-ingredients__note">
                                (<?= htmlspecialchars($ing->note) ?>)
                            </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <!-- Postup -->
    <section class="recipe-steps">
        <h2>Postup přípravy</h2>
        <?php if ($steps === []): ?>
            <p>Recept zatím nemá zadaný postup.</p>
        <?php else: ?>
            <ol>
                <?php foreach ($steps as $step): ?>
                    <li>
                        <?= htmlspecialchars($step->description) ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
