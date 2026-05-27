<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$recipeRepo = new RecipeRepository();

if (isset($_GET['featured'])) {
    $limit   = max(1, min(24, (int) ($_GET['limit'] ?? 6)));
    $recipes = $recipeRepo->getFeaturedHourly($limit);
} else {
    $recipes = $recipeRepo->getAll();
}

echo json_encode(array_map(fn($r) => [
    'id'           => $r->id,
    'slug'         => $r->slug,
    'name'         => $r->name,
    'description'  => $r->description,
    'image'        => $r->image,
    'totalTime'    => $r->getFormattedTotalTime(),
    'servings'     => $r->servings,
    'difficulty'   => $r->difficultyName,
    'category'     => $r->categoryName,
    'categorySlug' => $r->categorySlug,
], $recipes));
