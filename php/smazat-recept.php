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
    $recipeRepo->delete($recipe->id);
    header('Location: recepty.php');
    exit;
}

$pageTitle      = 'Smazat: ' . $recipe->name . ' – Kottyho kuchařka';
$favoritesCount = $favorites->count();

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main>
    <div class="confirmation-container" style="max-width:600px;margin:60px auto;background:#fff;padding:50px 40px;border-radius:20px;box-shadow:0 8px 30px rgba(0,0,0,0.08);text-align:center;">

        <div style="font-size:3rem;margin-bottom:20px;">🗑️</div>

        <h1 style="font-size:1.8rem;margin-bottom:12px;color:#333;">Smazat recept?</h1>
        <p style="color:#666;margin-bottom:8px;">Opravdu chceš trvale smazat recept:</p>
        <p style="font-size:1.3rem;font-weight:700;color:#e26a2c;margin-bottom:32px;">
            <?= htmlspecialchars($recipe->name) ?>
        </p>
        <p style="color:#999;font-size:0.9rem;margin-bottom:32px;">Tato akce je nevratná. Recept, jeho ingredience a postup budou odstraněny.</p>

        <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
            <form method="post">
                <button type="submit" name="confirm_delete"
                    style="background:#d32f2f;color:#fff;border:none;padding:14px 36px;border-radius:50px;font-size:1rem;font-weight:600;cursor:pointer;transition:background 0.3s;">
                    Ano, smazat
                </button>
            </form>
            <a href="recept.php?slug=<?= urlencode($recipe->slug) ?>"
                style="background:#fff;color:#333;border:2px solid #ddd;padding:12px 36px;border-radius:50px;font-size:1rem;font-weight:600;text-decoration:none;align-self:center;transition:border-color 0.3s;">
                ← Zrušit
            </a>
        </div>

    </div>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>