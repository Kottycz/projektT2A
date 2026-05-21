<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$categoryRepo = new CategoryRepository();
$favorites    = new Favorites();

$categories = $categoryRepo->getAll();

$pageTitle      = 'Kategorie receptů – Kottyho kuchařka';
$favoritesCount = $favorites->count();

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main>
    <section class="category-header">
        <h1>Kategorie receptů</h1>
        <p>Vyberte si kategorii a nechte se inspirovat.</p>
    </section>

    <section class="recipe-categories">
        <div class="recipe-categories-inner">
            <div class="recipe-categories-grid">
                <?php foreach ($categories as $cat): ?>
                    <a href="kategorie.php?slug=<?= urlencode($cat->slug) ?>" class="recipe-category">
                        <img src="../<?= htmlspecialchars($cat->image) ?>" alt="<?= htmlspecialchars($cat->name) ?>">
                        <span><?= htmlspecialchars($cat->name) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
