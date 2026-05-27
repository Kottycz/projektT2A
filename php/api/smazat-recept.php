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

$recipeRepo = new RecipeRepository();
$slug       = trim($_POST['slug'] ?? '');
$recipe     = $slug !== '' ? $recipeRepo->getBySlug($slug) : null;

if ($recipe === null) {
    http_response_code(404);
    echo json_encode(['error' => 'Recept nenalezen']);
    exit;
}

$recipeRepo->delete($recipe->id);

echo json_encode(['success' => true]);
