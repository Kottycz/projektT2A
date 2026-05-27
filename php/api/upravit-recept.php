<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metoda není povolena']);
    exit;
}

csrf_verify();

$recipeRepo   = new RecipeRepository();
$categoryRepo = new CategoryRepository();
$diffRepo     = new DifficultyRepository();

$slug   = trim($_POST['slug'] ?? '');
$recipe = $slug !== '' ? $recipeRepo->getBySlug($slug) : null;

if ($recipe === null) {
    http_response_code(404);
    echo json_encode(['error' => 'Recept nenalezen']);
    exit;
}

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

$allowedCat  = array_map(fn($c) => $c->id, $categoryRepo->getAll());
$allowedDiff = array_map(fn($d) => $d->id, $diffRepo->getAll());

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

if (!$v->isValid()) {
    http_response_code(422);
    echo json_encode(['errors' => $v->getErrors()]);
    exit;
}

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

echo json_encode(['slug' => $recipe->slug]);
