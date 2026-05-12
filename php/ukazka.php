<?php

declare(strict_types=1);

/**
 * UKÁZKOVÁ STRÁNKA – jak používat repozitáře, oblíbené a partials
 *
 * Spuštění:
 *   1. Nejdřív vytvořte databázi:  php projekt-kotrba/database/init.php
 *   2. Spusťte PHP server:         php -S localhost:8080 -t projekt-kotrba
 *   3. Otevřete v prohlížeči:      http://localhost:8080/ukazka.php
 *
 * Co tato stránka ukazuje:
 *   - Načtení všech tříd přes bootstrap.php
 *   - Znovupoužití částí stránky (header, footer, recipe-card) přes require
 *   - Práce s RecipeRepository (načtení doporučených receptů)
 *   - Práce s Favorites (přidání/odebrání z oblíbených, zobrazení počtu)
 *   - Vypsání dat z DTO objektů v HTML šabloně
 */

// 1) Načteme všechny třídy
require_once __DIR__ . '/src/bootstrap.php';

// 2) Vytvoříme instance repozitáře a oblíbených
$recipeRepo = new RecipeRepository();
$favorites = new Favorites();

// 3) Zpracování akce "přepnout oblíbený" (přišla z formuláře)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite'])) {
	$recipeId = (int) $_POST['recipe_id'];
	$recipe = $recipeRepo->getById($recipeId);

	if ($recipe !== null) {
		$favorites->toggle($recipe->id);
	}

	// Přesměrování zpět (Post/Redirect/Get – zabrání opakovanému odeslání formuláře)
	header('Location: ' . $_SERVER['REQUEST_URI']);
	exit;
}

// 4) Načteme data pro šablonu
$featuredRecipes = $recipeRepo->getFeatured(limit: 6);
$favoritesCount = $favorites->count();

// 5) Proměnné pro header partial
$pageTitle = 'Kottyho kuchařka – ukázka';

?>
<?php
// ============================================================
// HEADER – společná hlavička pro všechny stránky
// Partial očekává proměnné: $pageTitle, $favoritesCount
// ============================================================
require __DIR__ . '/partials/header.php';
?>

<main class="container">
    <h1 class="section-title">Doporučené recepty</h1>

    <div class="recipes-grid">
        <?php
        // ============================================================
        // RECIPE CARD – opakovaně použitá komponenta
        // Partial očekává proměnnou: $recipe (RecipeDTO)
        // Volitelně: $isFavorite (bool)
        // ============================================================
        foreach ($featuredRecipes as $recipe):
            $isFavorite = $favorites->contains($recipe->id);
            require __DIR__ . '/partials/recipe-card.php';
        endforeach;
        ?>
    </div>

    <!-- ============================================================
         INFO BOX – vysvětlení pro studenta
         ============================================================ -->
    <div class="info-box">
        <h2>Jak fungují partials?</h2>
        <p>
            Místo kopírování HTML hlavičky a patičky do každého souboru použijete
            <code>require</code> a PHP vloží obsah automaticky:
        </p>
        <pre><code>&lt;?php
// Proměnné, které partial potřebuje
$pageTitle = 'Hlavní stránka';
$favoritesCount = $favorites->count();

// Vložení hlavičky (otevře &lt;html&gt;, &lt;head&gt;, &lt;header&gt;)
require __DIR__ . '/partials/header.php';
?&gt;

&lt;!-- Zde je obsah konkrétní stránky --&gt;

&lt;?php
// Vložení patičky (uzavře &lt;footer&gt;, &lt;/body&gt;, &lt;/html&gt;)
require __DIR__ . '/partials/footer.php';
?&gt;</code></pre>

        <h2>Jak funguje karta receptu?</h2>
        <p>
            Partial <code>recipe-card.php</code> očekává proměnnou <code>$recipe</code>.
            Ve smyčce se tak karta použije opakovaně pro každý recept:
        </p>
        <pre><code>&lt;?php foreach ($recipes as $recipe): ?&gt;
    &lt;?php $isFavorite = $favorites-&gt;contains($recipe-&gt;id); ?&gt;
    &lt;?php require __DIR__ . '/partials/recipe-card.php'; ?&gt;
&lt;?php endforeach; ?&gt;</code></pre>

        <h2>Soubory partials</h2>
        <p>Všechny znovupoužitelné části stránek jsou ve složce <code>partials/</code>:</p>
        <ul>
            <li><code>partials/header.php</code> – hlavička s navigací a počitadlem oblíbených</li>
            <li><code>partials/footer.php</code> – patička</li>
            <li><code>partials/recipe-card.php</code> – karta receptu s tlačítkem oblíbené</li>
        </ul>
    </div>
</main>

<?php
// ============================================================
// FOOTER – společná patička pro všechny stránky
// ============================================================
require __DIR__ . '/partials/footer.php';
?>
