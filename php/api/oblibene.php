<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$recipeRepo = new RecipeRepository();
$favorites  = new Favorites();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $recipeId = (int) ($_POST['recipe_id'] ?? 0);
    $recipe   = $recipeRepo->getById($recipeId);

    if ($recipe === null) {
        http_response_code(404);
        echo json_encode(['error' => 'Recept nenalezen']);
        exit;
    }

    $favorites->toggle($recipeId);
    session_write_close();

    echo json_encode([
        'isFavorite' => $favorites->contains($recipeId),
        'count'      => $favorites->count(),
    ]);
    exit;
}

$recipes = $recipeRepo->getByIds($favorites->getIds());

echo json_encode([
    'count'   => $favorites->count(),
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
