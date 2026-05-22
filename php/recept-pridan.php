<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$recipeRepo = new RecipeRepository();
$favorites  = new Favorites();

$slug   = trim($_GET['slug'] ?? '');
$recipe = $slug !== '' ? $recipeRepo->getBySlug($slug) : null;

if ($recipe === null) {
    header('Location: recepty.php');
    exit;
}

$pageTitle      = 'Recept přidán – Kottyho kuchařka';
$favoritesCount = $favorites->count();

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main>
    <div class="confirmation-container">
        <div class="success-icon">✅</div>
        <h1>Recept byl přidán!</h1>
        <p class="recipe-name"><?= htmlspecialchars($recipe->name) ?></p>
        <p style="color:#666;margin-bottom:32px;">Tvůj recept byl úspěšně uložen a je hned dostupný.</p>
        <div class="action-buttons">
            <a href="recept.php?slug=<?= urlencode($recipe->slug) ?>" class="btn-primary">Zobrazit recept →</a>
            <a href="pridat-recept.php" class="btn-secondary">Přidat další recept</a>
            <a href="recepty.php" class="btn-secondary">← Zpět na recepty</a>
        </div>
    </div>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
