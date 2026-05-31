<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$recipeRepo = new RecipeRepository();
$favorites  = new Favorites();

$slug   = trim($_GET['slug'] ?? '');
$recipe = $slug !== '' ? $recipeRepo->getBySlug($slug) : null;

if ($recipe === null) {
    http_response_code(404);
    header('Location: recepty.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    csrf_verify();
    $recipeRepo->delete($recipe->id);
    header('Location: recepty.php');
    exit;
}

$pageTitle      = 'Smazat: ' . $recipe->name . ' – Kottyho kuchařka';
$favoritesCount = $favorites->count();

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main>
    <div class="confirmation-container">

        <div class="success-icon">🗑️</div>

        <h1>Smazat recept?</h1>
        <p class="delete-confirm-lead">Opravdu chceš trvale smazat recept:</p>
        <p class="delete-confirm-name"><?= htmlspecialchars($recipe->name) ?></p>
        <p class="delete-confirm-warning">Tato akce je nevratná. Recept, jeho ingredience a postup budou odstraněny.</p>

        <div class="btn-group">
            <form method="post">
                <?= csrf_field() ?>
                <button type="submit" name="confirm_delete" class="btn-action btn-action--red">
                    Ano, smazat
                </button>
            </form>
            <a href="recept.php?slug=<?= urlencode($recipe->slug) ?>" class="btn-cancel">
                ← Zrušit
            </a>
        </div>

    </div>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
