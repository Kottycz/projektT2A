<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$recipeRepo   = new RecipeRepository();
$categoryRepo = new CategoryRepository();
$diffRepo     = new DifficultyRepository();
$favorites    = new Favorites();

$slug   = trim($_GET['slug'] ?? '');
$recipe = $slug !== '' ? $recipeRepo->getBySlug($slug) : null;

if ($recipe === null) {
    http_response_code(404);
    header('Location: recepty.php');
    exit;
}

$categories   = $categoryRepo->getAll();
$difficulties = $diffRepo->getAll();
$ingredients  = $recipeRepo->getIngredients($recipe->id);
$steps        = $recipeRepo->getSteps($recipe->id);

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image       = trim($_POST['image'] ?? '');
    $categoryId  = trim($_POST['category_id'] ?? '');
    $diffId      = trim($_POST['difficulty_id'] ?? '');
    $prepTime    = trim($_POST['prep_time'] ?? '');
    $cookTime    = trim($_POST['cook_time'] ?? '');
    $servings    = trim($_POST['servings'] ?? '');
    $ingText     = trim($_POST['ingredients'] ?? '');
    $stepsText   = trim($_POST['steps'] ?? '');

    $allowedCat  = array_map(fn($c) => $c->id, $categories);
    $allowedDiff = array_map(fn($d) => $d->id, $difficulties);

    $v = new Validator();
    $v->required('name', $name, 'Název je povinný.')
      ->maxLength('name', $name, 200, 'Název nesmí být delší než 200 znaků.')
      ->required('category_id', $categoryId, 'Vyberte kategorii.')
      ->in('category_id', (int) $categoryId, $allowedCat, 'Neplatná kategorie.')
      ->required('difficulty_id', $diffId, 'Vyberte obtížnost.')
      ->in('difficulty_id', (int) $diffId, $allowedDiff, 'Neplatná obtížnost.')
      ->required('prep_time', $prepTime, 'Doba přípravy je povinná.')
      ->intRange('prep_time', $prepTime, 0, 600, 'Doba přípravy musí být 0–600 minut.')
      ->required('cook_time', $cookTime, 'Doba vaření je povinná.')
      ->intRange('cook_time', $cookTime, 0, 600, 'Doba vaření musí být 0–600 minut.')
      ->required('servings', $servings, 'Počet porcí je povinný.')
      ->intRange('servings', $servings, 1, 50, 'Počet porcí musí být 1–50.');

    if ($v->isValid()) {
        $recipeRepo->update(
            id:              $recipe->id,
            categoryId:      (int) $categoryId,
            difficultyId:    (int) $diffId,
            name:            $name,
            description:     $description,
            image:           $image !== '' ? $image : $recipe->image,
            prepTimeMinutes: (int) $prepTime,
            cookTimeMinutes: (int) $cookTime,
            servings:        (int) $servings,
        );

        $ingLines = array_values(array_filter(
            array_map('trim', explode("\n", $ingText)),
            fn($l) => $l !== '',
        ));
        $stepLines = array_values(array_filter(
            array_map('trim', explode("\n", $stepsText)),
            fn($l) => $l !== '',
        ));

        if ($ingLines !== []) {
            $recipeRepo->replaceIngredients($recipe->id, $ingLines);
        }
        if ($stepLines !== []) {
            $recipeRepo->replaceSteps($recipe->id, $stepLines);
        }

        header('Location: recept.php?slug=' . urlencode($recipe->slug));
        exit;
    } else {
        $errors = $v->getErrors();
    }
}

// Předvyplnění ingrediencí a kroků
$ingDefault  = implode("\n", array_map(fn($i) => trim($i->getFormattedAmount() . ' ' . $i->name), $ingredients));
$stepsDefault = implode("\n", array_map(fn($s) => $s->description, $steps));

$pageTitle      = 'Upravit: ' . $recipe->name . ' – Kottyho kuchařka';
$favoritesCount = $favorites->count();

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main>
    <div class="add-recipe-container">
        <h1>Upravit recept</h1>

        <?php if ($errors !== []): ?>
            <div class="form-errors">
                <p>Opravte prosím chyby:</p>
                <ul>
                    <?php foreach ($errors as $msg): ?>
                        <li><?= htmlspecialchars($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="recipe-form" method="post">
            <?= csrf_field() ?>

            <fieldset>
                <legend>Základní informace</legend>

                <label for="name">Název receptu *</label>
                <input type="text" id="name" name="name"
                       value="<?= htmlspecialchars($_POST['name'] ?? $recipe->name) ?>" required>

                <label for="description">Popis</label>
                <textarea id="description" name="description"><?= htmlspecialchars($_POST['description'] ?? $recipe->description) ?></textarea>

                <label for="image">Cesta k obrázku</label>
                <input type="text" id="image" name="image"
                       value="<?= htmlspecialchars($_POST['image'] ?? $recipe->image) ?>"
                       placeholder="assets/images/nazev.jpg">
                <small class="field-hint">Aktuální: <?= htmlspecialchars($recipe->image) ?></small>

                <div class="form-two-col">
                    <div>
                        <label for="category_id">Kategorie *</label>
                        <select id="category_id" name="category_id" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat->id ?>"
                                    <?= (($_POST['category_id'] ?? $recipe->categoryId) == $cat->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="difficulty_id">Obtížnost *</label>
                        <select id="difficulty_id" name="difficulty_id" required>
                            <?php foreach ($difficulties as $diff): ?>
                                <option value="<?= $diff->id ?>"
                                    <?= (($_POST['difficulty_id'] ?? $recipe->difficultyId) == $diff->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($diff->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label for="prep_time">Příprava (min) *</label>
                        <input type="number" id="prep_time" name="prep_time" min="0" max="600"
                               value="<?= htmlspecialchars((string) ($_POST['prep_time'] ?? $recipe->prepTimeMinutes)) ?>" required>
                    </div>
                    <div>
                        <label for="cook_time">Vaření (min) *</label>
                        <input type="number" id="cook_time" name="cook_time" min="0" max="600"
                               value="<?= htmlspecialchars((string) ($_POST['cook_time'] ?? $recipe->cookTimeMinutes)) ?>" required>
                    </div>
                    <div>
                        <label for="servings">Porcí *</label>
                        <input type="number" id="servings" name="servings" min="1" max="50"
                               value="<?= htmlspecialchars((string) ($_POST['servings'] ?? $recipe->servings)) ?>" required>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Ingredience a postup</legend>

                <label for="ingredients">Ingredience <small>(každá na nový řádek, prázdné pole = beze změny)</small></label>
                <textarea id="ingredients" name="ingredients" rows="10"><?= htmlspecialchars($_POST['ingredients'] ?? $ingDefault) ?></textarea>

                <label for="steps">Postup přípravy <small>(každý krok na nový řádek, prázdné pole = beze změny)</small></label>
                <textarea id="steps" name="steps" rows="10"><?= htmlspecialchars($_POST['steps'] ?? $stepsDefault) ?></textarea>
            </fieldset>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Uložit změny</button>
                <a href="recept.php?slug=<?= urlencode($recipe->slug) ?>" class="btn-secondary">← Zrušit</a>
            </div>
        </form>
    </div>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
