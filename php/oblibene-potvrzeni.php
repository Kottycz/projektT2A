<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$favorites = new Favorites();

$pageTitle      = 'Recept přidán – Kottyho kuchařka';
$favoritesCount = $favorites->count();

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main>
    <div class="confirmation-container">
        <div class="success-icon">🎉</div>
        <h1>Recept byl úspěšně přidán do oblíbených</h1>

        <div class="action-buttons">
            <a href="oblibene.php" class="btn-primary">Zobrazit oblíbené recepty</a>
            <a href="recepty.php" class="btn-secondary">Zpět na recepty</a>
        </div>
    </div>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
