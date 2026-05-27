<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$recipeRepo = new RecipeRepository();
$favorites  = new Favorites();

$slug   = trim($_GET['slug'] ?? '');
$recipe = $slug !== '' ? $recipeRepo->getBySlug($slug) : null;

if ($recipe === null) {
    http_response_code(404);
    echo json_encode(['error' => 'Recept nenalezen']);
    exit;
}

$ingredients = $recipeRepo->getIngredients($recipe->id);
$steps       = $recipeRepo->getSteps($recipe->id);
$isFavorite  = $favorites->contains($recipe->id);

echo json_encode([
    'id'           => $recipe->id,
    'slug'         => $recipe->slug,
    'name'         => $recipe->name,
    'description'  => $recipe->description,
    'image'        => $recipe->image,
    'totalTime'    => $recipe->getFormattedTotalTime(),
    'prepTime'     => $recipe->prepTimeMinutes,
    'cookTime'     => $recipe->cookTimeMinutes,
    'servings'     => $recipe->servings,
    'difficulty'   => $recipe->difficultyName,
    'category'     => $recipe->categoryName,
    'categorySlug' => $recipe->categorySlug,
    'isFavorite'   => $isFavorite,
    'ingredients'  => array_map(fn($i) => [
        'name'   => $i->name,
        'amount' => $i->getFormattedAmount(),
        'note'   => $i->note,
    ], $ingredients),
    'steps' => array_map(fn($s) => $s->description, $steps),
]);
