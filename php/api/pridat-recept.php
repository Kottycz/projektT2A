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

$recipeRepo     = new RecipeRepository();
$categoryRepo   = new CategoryRepository();
$difficultyRepo = new DifficultyRepository();

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

$allowedCategoryIds   = array_map(fn($c) => $c->id, $categoryRepo->getAll());
$allowedDifficultyIds = array_map(fn($d) => $d->id, $difficultyRepo->getAll());

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

if (!$v->isValid()) {
    http_response_code(422);
    echo json_encode(['errors' => $v->getErrors()]);
    exit;
}

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

echo json_encode(['slug' => $slug, 'name' => $name]);
