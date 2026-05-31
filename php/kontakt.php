<?php

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$favorites = new Favorites();

$pageTitle      = 'Kontakt – Kottyho kuchařka';
$favoritesCount = $favorites->count();

$errors = [];
$sent   = isset($_GET['sent']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $v = new Validator();
    $v->required('name', $name, 'Jméno je povinné.')
      ->required('email', $email, 'E-mail je povinný.')
      ->email('email', $email, 'Zadejte platný e-mail.')
      ->required('message', $message, 'Zpráva je povinná.');

    if ($v->isValid()) {
        session_write_close();
        header('Location: kontakt.php?sent=1');
        exit;
    }

    $errors = $v->getErrors();
}

$name    ??= '';
$email   ??= '';
$message ??= '';

?>
<?php require __DIR__ . '/partials/header.php'; ?>

<main class="contact-page">
    <section class="contact-container">
        <h1>Kontakt</h1>

        <?php if ($sent): ?>
            <p class="success-message">✅ Zpráva byla odeslána. Brzy se vám ozveme!</p>
        <?php endif; ?>

        <div class="contact-info">
            <p><strong>Email:</strong> info@kucharka.cz</p>
            <p><strong>Telefon:</strong> +420 123 456 789</p>
            <p><strong>Adresa:</strong> Ulice 1, 110 00 Praha</p>
        </div>

        <?php if ($errors !== []): ?>
            <div class="form-errors">
                <p>Opravte prosím chyby:</p>
                <ul>
                    <?php foreach ($errors as $msg): ?>
                        <li><?= htmlspecialchars($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form class="contact-form" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <div>
                    <input type="text" name="name" placeholder="Vaše jméno"
                           value="<?= htmlspecialchars($name) ?>" required>
                    <?php if (isset($errors['name'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['name']) ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <input type="email" name="email" placeholder="Váš e-mail"
                           value="<?= htmlspecialchars($email) ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['email']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <textarea name="message" placeholder="Vaše zpráva" rows="5" required><?= htmlspecialchars($message) ?></textarea>
                <?php if (isset($errors['message'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['message']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit">Odeslat zprávu</button>
        </form>
    </section>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>
