<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$favorites = new Favorites();

$pageTitle      = 'O nás – Kottyho kuchařka';
$favoritesCount = $favorites->count();

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main>
    <section class="about-section">
        <h1>O nás</h1>
        <p>
            Vítejte v <span class="highlight">Kottyho kuchařce</span>!
            Tato webová kuchařka vznikla jako školní projekt s cílem vytvořit
            přehledné a inspirativní místo pro všechny milovníky dobrého jídla.
        </p>
        <p>
            Najdete zde recepty pečlivě rozdělené do kategorií – od rychlých
            snídaní až po luxusní dezerty. Naším cílem je ukázat, že vaření
            může být radost, nikoliv povinnost.
        </p>
    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
