<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$categoryRepo   = new CategoryRepository();
$difficultyRepo = new DifficultyRepository();

echo json_encode([
    'categories' => array_map(fn($c) => [
        'id'   => $c->id,
        'name' => $c->name,
    ], $categoryRepo->getAll()),
    'difficulties' => array_map(fn($d) => [
        'id'   => $d->id,
        'name' => $d->name,
    ], $difficultyRepo->getAll()),
]);
