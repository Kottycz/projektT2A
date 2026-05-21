<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$favorites = new Favorites();

$pageTitle      = 'Kontakt – Kottyho kuchařka';
$favoritesCount = $favorites->count();

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main class="contact-page">
    <section class="contact-container">
        <h1>Kontakt</h1>

        <div class="contact-info">
            <p><strong>Email:</strong> info@kucharka.cz</p>
            <p><strong>Telefon:</strong> +420 123 456 789</p>
            <p><strong>Adresa:</strong> Ulice 1, 110 00 Praha</p>
        </div>

        <form class="contact-form">
            <div class="form-group">
                <input type="text" placeholder="Vaše jméno" required>
                <input type="email" placeholder="Váš e-mail" required>
            </div>
            <textarea placeholder="Vaše zpráva" rows="5" required></textarea>
            <button type="submit" class="btn-submit">Odeslat zprávu</button>
        </form>
    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
