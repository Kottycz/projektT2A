<?php

/**
 * PARTIAL: Hlavička stránky
 *
 * Očekává proměnnou:
 *   $pageTitle (string) – titulek stránky
 *
 * Volitelně:
 *   $favoritesCount (int) – počet receptů v oblíbených (výchozí 0)
 */

$pageTitle ??= 'Kottyho kuchařka';
$favoritesCount ??= 0;

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="header__logo">Kottyho kuchařka</a>

    <nav class="header__nav">
        <a href="index.php">Domů</a>
        <a href="kategorie.php">Kategorie</a>
        <a href="recepty.php">Recepty</a>
        <a href="pridat-recept-1.php">Přidat recept</a>
        <a href="o-nas.php">O nás</a>
        <a href="kontakt.php">Kontakt</a>
    </nav>

    <a href="oblibene.php" class="header__favorites" title="Oblíbené recepty">
        &#9825;
        <?php if ($favoritesCount > 0): ?>
            <span class="header__favorites-badge"><?= $favoritesCount ?></span>
        <?php endif; ?>
    </a>
</header>
