<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$recipeRepo     = new RecipeRepository();
$categoryRepo   = new CategoryRepository();
$difficultyRepo = new DifficultyRepository();
$favorites      = new Favorites();

$categories   = $categoryRepo->getAll();
$difficulties = $difficultyRepo->getAll();

$errors = [];

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
    $ingredients = trim($_POST['ingredients'] ?? '');
    $steps       = trim($_POST['steps'] ?? '');

    $allowedCategoryIds   = array_map(fn($c) => $c->id, $categories);
    $allowedDifficultyIds = array_map(fn($d) => $d->id, $difficulties);

    $v = new Validator();
    $v->required('name', $name, 'Název receptu je povinný.')
      ->maxLength('name', $name, 200, 'Název nesmí být delší než 200 znaků.')
      ->required('category_id', $categoryId, 'Vyberte kategorii.')
      ->in('category_id', (int) $categoryId, $allowedCategoryIds, 'Neplatná kategorie.')
      ->required('difficulty_id', $diffId, 'Vyberte obtížnost.')
      ->in('difficulty_id', (int) $diffId, $allowedDifficultyIds, 'Neplatná obtížnost.')
      ->required('prep_time', $prepTime, 'Doba přípravy je povinná.')
      ->intRange('prep_time', $prepTime, 1, 600, 'Doba přípravy musí být 1–600 minut.')
      ->required('cook_time', $cookTime, 'Doba vaření je povinná.')
      ->intRange('cook_time', $cookTime, 0, 600, 'Doba vaření musí být 0–600 minut.')
      ->required('servings', $servings, 'Počet porcí je povinný.')
      ->intRange('servings', $servings, 1, 50, 'Počet porcí musí být 1–50.')
      ->required('ingredients', $ingredients, 'Zadejte alespoň jednu ingredienci.')
      ->required('steps', $steps, 'Zadejte alespoň jeden krok postupu.');

    if ($v->isValid()) {
        $ingredientLines = array_values(array_filter(
            array_map('trim', explode("\n", $ingredients)),
            fn($l) => $l !== '',
        ));
        $stepLines = array_values(array_filter(
            array_map('trim', explode("\n", $steps)),
            fn($l) => $l !== '',
        ));

        $slug = $recipeRepo->create(
            categoryId:      (int) $categoryId,
            difficultyId:    (int) $diffId,
            name:            $name,
            description:     $description,
            image:           $image !== '' ? $image : 'assets/images/hlavni-jidla.jpeg',
            prepTimeMinutes: (int) $prepTime,
            cookTimeMinutes: (int) $cookTime,
            servings:        (int) $servings,
        );

        $newRecipe = $recipeRepo->getBySlug($slug);
        $recipeRepo->replaceIngredients($newRecipe->id, $ingredientLines);
        $recipeRepo->replaceSteps($newRecipe->id, $stepLines);

        header('Location: recept-pridan.php?slug=' . urlencode($slug));
        exit;
    } else {
        $errors = $v->getErrors();
    }
}

$pageTitle      = 'Přidat recept – Kottyho kuchařka';
$favoritesCount = $favorites->count();

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main>
    <div class="add-recipe-container">
        <h1>Přidat nový recept</h1>

        <?php if ($errors !== []): ?>
            <div class="form-errors" style="background:#fff0ee;border:1px solid #e26a2c;border-radius:8px;padding:16px;margin-bottom:24px;">
                <p style="margin:0 0 8px;font-weight:600;">Opravte prosím chyby:</p>
                <ul style="margin:0;padding-left:20px;">
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
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       placeholder="Název receptu" required>

                <label for="description">Popis</label>
                <textarea id="description" name="description" placeholder="Krátký popis receptu"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

                <label for="image">Cesta k obrázku</label>
                <input type="text" id="image" name="image"
                       value="<?= htmlspecialchars($_POST['image'] ?? '') ?>"
                       placeholder="assets/images/nazev.jpg">
                <small>Nechej prázdné pro výchozí obrázek.</small>

                <div class="form-two-col">
                    <div>
                        <label for="category_id">Kategorie *</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">– Vyberte kategorii –</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat->id ?>"
                                    <?= (($_POST['category_id'] ?? '') == $cat->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="difficulty_id">Obtížnost *</label>
                        <select id="difficulty_id" name="difficulty_id" required>
                            <option value="">– Vyberte obtížnost –</option>
                            <?php foreach ($difficulties as $diff): ?>
                                <option value="<?= $diff->id ?>"
                                    <?= (($_POST['difficulty_id'] ?? '') == $diff->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($diff->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label for="prep_time">Příprava (min) *</label>
                        <input type="number" id="prep_time" name="prep_time" min="1" max="600"
                               value="<?= htmlspecialchars($_POST['prep_time'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label for="cook_time">Vaření (min) *</label>
                        <input type="number" id="cook_time" name="cook_time" min="0" max="600"
                               value="<?= htmlspecialchars($_POST['cook_time'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label for="servings">Porcí *</label>
                        <input type="number" id="servings" name="servings" min="1" max="50"
                               value="<?= htmlspecialchars($_POST['servings'] ?? '') ?>" required>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Ingredience a postup</legend>

                <label for="ingredients">Ingredience * <small>(každou na nový řádek)</small></label>
                <textarea id="ingredients" name="ingredients" rows="8"
                          placeholder="200 g mouky&#10;3 vejce&#10;100 ml mléka" required><?= htmlspecialchars($_POST['ingredients'] ?? '') ?></textarea>

                <label for="steps">Postup přípravy * <small>(každý krok na nový řádek)</small></label>
                <textarea id="steps" name="steps" rows="8"
                          placeholder="Smíchejte mouku s vejci.&#10;Přidejte mléko a promíchejte.&#10;Smažte na pánvi." required><?= htmlspecialchars($_POST['steps'] ?? '') ?></textarea>
            </fieldset>

            <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
                <button type="submit" class="btn-submit">Přidat recept</button>
                <a href="recepty.php" class="btn-secondary" style="align-self:center;">← Zrušit</a>
            </div>
        </form>
    </div>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
