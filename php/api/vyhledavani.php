<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$recipeRepo = new RecipeRepository();
$query      = trim($_GET['q'] ?? '');

if ($query === '') {
    echo json_encode(['query' => '', 'recipes' => []]);
    exit;
}

$recipes = $recipeRepo->search($query);

echo json_encode([
    'query'   => $query,
    'recipes' => array_map(fn($r) => [
        'id'          => $r->id,
        'slug'        => $r->slug,
        'name'        => $r->name,
        'description' => $r->description,
        'image'       => $r->image,
        'totalTime'   => $r->getFormattedTotalTime(),
        'servings'    => $r->servings,
        'difficulty'  => $r->difficultyName,
    ], $recipes),
]);
