<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$categoryRepo = new CategoryRepository();
$recipeRepo   = new RecipeRepository();
$slug         = trim($_GET['slug'] ?? '');

if ($slug !== '') {
    $category = $categoryRepo->getBySlug($slug);

    if ($category === null) {
        http_response_code(404);
        echo json_encode(['error' => 'Kategorie nenalezena']);
        exit;
    }

    $recipes = $recipeRepo->getByCategorySlug($slug);

    echo json_encode([
        'category' => [
            'id'          => $category->id,
            'name'        => $category->name,
            'slug'        => $category->slug,
            'description' => $category->description,
            'image'       => $category->image,
        ],
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
} else {
    $categories = $categoryRepo->getAll();

    echo json_encode(array_map(fn($c) => [
        'id'          => $c->id,
        'name'        => $c->name,
        'slug'        => $c->slug,
        'description' => $c->description,
        'image'       => $c->image,
    ], $categories));
}
