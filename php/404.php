<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

http_response_code(404);

$favorites = new Favorites();

$pageTitle      = 'Stránka nenalezena – Kottyho kuchařka';
$favoritesCount = $favorites->count();

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main>
    <section class="category-header">
        <h1>404 – Stránka nenalezena</h1>
        <p>Požadovaná stránka neexistuje nebo byla přesunuta.</p>
    </section>

    <div class="confirmation-container">
        <div class="success-icon">🔍</div>
        <p class="confirmation-text">Omlouváme se, ale tato stránka neexistuje.</p>
        <div class="action-buttons">
            <a href="index.php" class="btn-primary">← Zpět na úvodní stránku</a>
            <a href="recepty.php" class="btn-secondary">Procházet recepty</a>
        </div>
    </div>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
