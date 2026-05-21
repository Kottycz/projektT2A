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
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>

<header class="header">
    <div class="logo">
        <img src="../assets/images/logo2.png" alt="Logo" class="logo-img">
        <a href="index.php" class="logo-link">
            <h1>Kottyho kuchařka</h1>
        </a>
    </div>

    <nav class="navigation">
        <ul>
            <li><a href="recepty.php">Recepty</a></li>
            <li>
                <a href="oblibene.php">
                    Oblíbené<?php if ($favoritesCount > 0): ?> <span class="header__favorites-badge"><?= $favoritesCount ?></span><?php endif; ?>
                </a>
            </li>
            <li><a href="../kontakt.html">Kontakt</a></li>
            <li><a href="../o-nas.html">O nás</a></li>
        </ul>
    </nav>
</header>
