# Kottyho kuchařka – 2. fáze (PHP + SQLite)

Připravený základ pro druhou fázi tvého projektu (individuální zadání – web s recepty), kde propojíš svůj HTML/CSS frontend z 1. fáze s PHP a SQLite databází.

## Spuštění

```bash
php projekt-kotrba/database/init.php
php -S localhost:8080 -t projekt-kotrba
```

Otevři v prohlížeči: `http://localhost:8080/ukazka.php`

Vzorové stránky:
- `/ukazka.php` – výpis receptů, oblíbené, partials
- `/recept.php?slug=svickova` – detail receptu s ingrediencemi, postupem a galerií

### Reset databáze

```bash
php projekt-kotrba/database/init.php
```

## Struktura projektu

```
projekt-kotrba/
├── database/
│   ├── init.php              ← skript pro vytvoření/reset databáze
│   └── kucharka.db           ← SQLite databáze (generuje se automaticky)
├── src/
│   ├── bootstrap.php         ← načte všechny třídy (stačí jeden require)
│   ├── Database.php          ← připojení k databázi
│   ├── Favorites.php         ← oblíbené recepty (ukládá do session)
│   ├── Validator.php         ← validátor formulářů (fluent interface)
│   ├── DTO/                  ← datové objekty (readonly třídy)
│   │   ├── CategoryDTO.php
│   │   ├── DifficultyDTO.php
│   │   ├── UnitDTO.php
│   │   ├── RecipeDTO.php
│   │   ├── RecipeImageDTO.php
│   │   ├── IngredientDTO.php
│   │   ├── RecipeStepDTO.php
│   │   ├── AuthorDTO.php
│   │   ├── SubmissionDTO.php
│   │   ├── SubmissionIngredientDTO.php
│   │   └── SubmissionStepDTO.php
│   └── Repository/           ← třídy pro práci s databází
│       ├── CategoryRepository.php
│       ├── DifficultyRepository.php
│       ├── UnitRepository.php
│       ├── RecipeRepository.php
│       ├── AuthorRepository.php
│       └── SubmissionRepository.php
├── partials/                 ← znovupoužitelné části stránek
│   ├── header.php            ← hlavička s navigací a počitadlem oblíbených
│   ├── footer.php            ← patička
│   └── recipe-card.php       ← karta receptu
├── assets/
│   ├── css/                  ← ukázkové CSS (nahraď vlastním z 1. fáze)
│   └── images/
│       ├── kategorie/        ← obrázky kategorií (6 ks)
│       └── recepty/          ← obrázky receptů + galerie
├── ukazka.php                ← vzorová stránka (partials + oblíbené + recepty)
├── recept.php                ← vzorový detail receptu (ingredience + postup + galerie)
└── README.md                 ← tento soubor
```

## Partials – znovupoužitelné části stránek

V 1. fázi jsi hlavičku a patičku kopíroval ručně. S PHP stačí použít `require` a části se vloží automaticky:

```php
<?php
require_once __DIR__ . '/src/bootstrap.php';

$favorites = new Favorites();
$pageTitle = 'Hlavní stránka';
$favoritesCount = $favorites->count();

// Hlavička (otevírá <html>, <head>, <header> s navigací a počitadlem oblíbených)
require __DIR__ . '/partials/header.php';
?>

<!-- Zde je obsah konkrétní stránky -->

<?php
// Patička (uzavírá <footer>, </body>, </html>)
require __DIR__ . '/partials/footer.php';
?>
```

### Karta receptu

Partial `recipe-card.php` očekává proměnnou `$recipe` (RecipeDTO). Ve smyčce se karta opakuje:

```php
<div class="recipes-grid">
    <?php foreach ($recipes as $recipe): ?>
        <?php $isFavorite = $favorites->contains($recipe->id); ?>
        <?php require __DIR__ . '/partials/recipe-card.php'; ?>
    <?php endforeach; ?>
</div>
```

### Vlastní partials

Stejným způsobem si můžeš vytvořit další partials – například breadcrumb, boční panel, vyhledávací formulář, krok formuláře „Přidat recept" apod.

## Jak používat na svých stránkách

Na začátku každého PHP souboru stačí načíst bootstrap a vytvořit instance tříd, které potřebuješ:

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

$recipeRepo = new RecipeRepository();
$categoryRepo = new CategoryRepository();
$favorites = new Favorites();
```

### Načítání dat z databáze

```php
// Všechny kategorie
$categories = $categoryRepo->getAll();

// Doporučené recepty (pro hlavní stránku)
$featured = $recipeRepo->getFeatured(limit: 6);

// Recepty v kategorii (podle ID nebo slugu)
$recipes = $recipeRepo->getByCategory(1);
$recipes = $recipeRepo->getByCategorySlug('hlavni-jidla');

// Konkrétní recept podle slugu (pro detail receptu)
$recipe = $recipeRepo->getBySlug('svickova');

// Obrázky, ingredience a postup
$images = $recipeRepo->getImages($recipe->id);
$ingredients = $recipeRepo->getIngredients($recipe->id);
$steps = $recipeRepo->getSteps($recipe->id);

// Vyhledávání (hledá v názvu, popisu i v názvu ingredience)
$results = $recipeRepo->search('česnek');

// Načtení několika receptů podle pole ID (pro stránku oblíbených)
$favoriteRecipes = $recipeRepo->getByIds($favorites->getIds());
```

### Práce s oblíbenými

```php
$favorites = new Favorites();

// Přidat/odebrat/přepnout recept
$favorites->add($recipe->id);
$favorites->remove($recipe->id);
$favorites->toggle($recipe->id);

// Zjištění stavu
$favorites->contains($recipe->id);   // bool
$favorites->count();                 // int
$favorites->isEmpty();               // bool

// Pole ID všech oblíbených (pro načtení z repozitáře)
$ids = $favorites->getIds();
```

### Číselníky – obtížnost a jednotky

```php
$difficultyRepo = new DifficultyRepository();
$unitRepo = new UnitRepository();

$difficulties = $difficultyRepo->getAll(); // Snadné / Střední / Náročné
$units = $unitRepo->getAll();              // g, kg, ml, l, ks, lž., …

// Konkrétní položka podle ID
$difficulty = $difficultyRepo->getById(2);
$unit = $unitRepo->getById(1);
```

### Přidání nového receptu (3-krokový formulář)

Stránka „Přidat recept" je rozdělená na 3 kroky (analogie 3-krokového košíku z e-shopu). Mezi kroky se data drží v session, finálně se uloží přes `SubmissionRepository`.

```php
$authorRepo = new AuthorRepository();
$submissionRepo = new SubmissionRepository();

// 1) Vytvořit (nebo najít) autora
$author = $authorRepo->getByEmail($email)
    ?? $authorRepo->create($name, $email);

// 2) Uložit recept včetně ingrediencí a kroků – jednou transakcí
$submission = $submissionRepo->create(
    authorId: $author->id,
    categoryId: 2,           // Hlavní jídla
    difficultyId: 1,         // Snadné
    name: 'Míchaná vajíčka',
    description: 'Nadýchaná míchaná vajíčka s pažitkou.',
    prepTimeMinutes: 5,
    cookTimeMinutes: 10,
    servings: 2,
    ingredients: [
        ['name' => 'vejce',    'amount' => 4.0,  'unit_id' => 5, 'note' => ''],
        ['name' => 'máslo',    'amount' => 30.0, 'unit_id' => 1, 'note' => ''],
        ['name' => 'sůl',      'amount' => null, 'unit_id' => null, 'note' => 'podle chuti'],
    ],
    steps: [
        'Vejce rozšlehejte v misce, osolte.',
        'Na pánvi rozpusťte máslo a vlijte vejce.',
        'Stále míchejte vařečkou, dokud se vejce nesrazí.',
    ],
);
```

### Výpis dat v HTML šabloně

```php
<?php foreach ($recipes as $recipe): ?>
    <div class="recipe-card">
        <img src="<?= htmlspecialchars($recipe->image) ?>"
             alt="<?= htmlspecialchars($recipe->name) ?>">
        <h2><?= htmlspecialchars($recipe->name) ?></h2>
        <p><?= htmlspecialchars($recipe->categoryName) ?></p>
        <p>Celkem: <?= htmlspecialchars($recipe->getFormattedTotalTime()) ?></p>
        <p>Obtížnost: <?= htmlspecialchars($recipe->difficultyName) ?></p>
        <p><?= $recipe->servings ?> porcí</p>
    </div>
<?php endforeach; ?>
```

## Obrázky

Projekt obsahuje **placeholder obrázky** (SVG) ve složce `assets/images/`:

```
assets/images/
├── kategorie/    ← obrázky kategorií (6 ks)
└── recepty/      ← obrázky receptů + galerie
```

Placeholdery jsou barevné SVG soubory s názvem receptu/kategorie. **Nahraď je vlastními obrázky** (JPG, PNG, WebP) a uprav cesty v `database/init.php`.

## Vzorová data

Databáze obsahuje **6 kategorií** a **18 receptů** s českou i mezinárodní kuchyní (polévky, hlavní jídla, bezmasá jídla, saláty, moučníky, nápoje). Data si můžeš upravit v souboru `database/init.php` a poté znovu spustit:

```bash
php projekt-kotrba/database/init.php
```

## Bezpečnost – CSRF ochrana formulářů

Formuláře, které mění data (přidání mezi oblíbené, odeslání receptu), by měly být chráněné proti CSRF útokům. CSRF (Cross-Site Request Forgery) je útok, kdy škodlivá stránka přiměje prohlížeč uživatele odeslat formulář na tvůj web bez jeho vědomí.

Ochrana spočívá v tom, že při zobrazení formuláře vygeneruješ náhodný token, uložíš ho do session a vložíš do formuláře jako skryté pole. Při zpracování pak ověříš, že token souhlasí:

```php
// Generování tokenu (na začátku stránky s formulářem)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Vložení do formuláře
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

// Ověření při zpracování
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Neplatný bezpečnostní token.');
}
```

Vzorové stránky `ukazka.php` a `recept.php` CSRF ochranu neobsahují, aby zůstaly co nejjednodušší. **Ve svých stránkách ji ale implementuj** – zejména u formuláře pro přidání receptu.

## Databázové tabulky

| Tabulka | Popis |
|---------|-------|
| `categories` | Kategorie receptů (název, slug, obrázek, popis) |
| `difficulties` | Číselník obtížnosti (Snadné / Střední / Náročné) |
| `units` | Číselník měrných jednotek (g, kg, ml, l, ks, lžíce, lžička, špetka…) |
| `recipes` | Recepty (název, popis, čas přípravy/vaření, počet porcí, obrázek, příznak doporučený) |
| `recipe_images` | Galerie obrázků receptu |
| `recipe_ingredients` | Suroviny receptu (název, množství, jednotka, poznámka) |
| `recipe_steps` | Kroky postupu seřazené podle `step_number` |
| `authors` | Autoři uživatelských receptů |
| `submissions` | Uživatelem zaslané recepty (čekající na schválení) |
| `submission_ingredients` | Suroviny zaslaného receptu |
| `submission_steps` | Kroky postupu zaslaného receptu |

## Mapování fáze 2: e-shop → kuchařka

Pro úplnost (kdyby ses chtěl podívat na kostru e-shopu pro inspiraci v `/projekt`):

| E-shop | Kuchařka |
|---|---|
| `products` | `recipes` |
| `product_parameters` (info+select) | rozděleno na `recipe_ingredients` (suroviny) a `recipe_steps` (postup) |
| `customers` | `authors` |
| `orders` + `order_items` | `submissions` + `submission_ingredients` + `submission_steps` |
| `shipping_methods` / `payment_methods` | místo nich jednodušší číselníky `difficulties` a `units` |
| `Cart` (s množstvím a variantami) | `Favorites` (jen seznam ID) |
| 3-krokový košík (adresa → doprava/platba → shrnutí) | 3-krokový „Přidat recept" (základní info → suroviny+postup → shrnutí) |
