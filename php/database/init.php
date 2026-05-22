<?php

declare(strict_types=1);

/**
 * Inicializace databáze – vytvoří tabulky a naplní vzorovými daty.
 *
 * Spuštění: php projekt-kotrba/database/init.php
 *
 * POZOR: Smaže existující databázi a vytvoří novou!
 */

$dbPath = __DIR__ . '/kucharka.db';

// Smazat existující databázi
if (file_exists($dbPath)) {
	unlink($dbPath);
	echo "Stará databáze smazána.\n";
}

$db = new PDO('sqlite:' . $dbPath, options: [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$db->exec('PRAGMA journal_mode = WAL');
$db->exec('PRAGMA foreign_keys = ON');

// ============================================================
// Vytvoření tabulek
// ============================================================

$db->exec('
    CREATE TABLE categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        image TEXT NOT NULL DEFAULT "",
        description TEXT NOT NULL DEFAULT ""
    )
');

$db->exec('
    CREATE TABLE difficulties (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        sort_order INTEGER NOT NULL DEFAULT 0
    )
');

$db->exec('
    CREATE TABLE units (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        abbreviation TEXT NOT NULL DEFAULT ""
    )
');

$db->exec('
    CREATE TABLE recipes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER NOT NULL,
        difficulty_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        description TEXT NOT NULL DEFAULT "",
        image TEXT NOT NULL DEFAULT "",
        prep_time_minutes INTEGER NOT NULL DEFAULT 0,
        cook_time_minutes INTEGER NOT NULL DEFAULT 0,
        servings INTEGER NOT NULL DEFAULT 1,
        featured INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id),
        FOREIGN KEY (difficulty_id) REFERENCES difficulties(id)
    )
');

$db->exec('
    CREATE TABLE recipe_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        recipe_id INTEGER NOT NULL,
        image TEXT NOT NULL,
        sort_order INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
    )
');

$db->exec('
    CREATE TABLE recipe_ingredients (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        recipe_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        amount REAL,
        unit_id INTEGER,
        note TEXT NOT NULL DEFAULT "",
        sort_order INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
        FOREIGN KEY (unit_id) REFERENCES units(id)
    )
');

$db->exec('
    CREATE TABLE recipe_steps (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        recipe_id INTEGER NOT NULL,
        step_number INTEGER NOT NULL,
        description TEXT NOT NULL,
        FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
    )
');

$db->exec('
    CREATE TABLE authors (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )
');

$db->exec('
    CREATE TABLE submissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        author_id INTEGER NOT NULL,
        category_id INTEGER NOT NULL,
        difficulty_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        description TEXT NOT NULL DEFAULT "",
        prep_time_minutes INTEGER NOT NULL DEFAULT 0,
        cook_time_minutes INTEGER NOT NULL DEFAULT 0,
        servings INTEGER NOT NULL DEFAULT 1,
        status TEXT NOT NULL DEFAULT "pending",
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (author_id) REFERENCES authors(id),
        FOREIGN KEY (category_id) REFERENCES categories(id),
        FOREIGN KEY (difficulty_id) REFERENCES difficulties(id)
    )
');

$db->exec('
    CREATE TABLE submission_ingredients (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        submission_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        amount REAL,
        unit_id INTEGER,
        note TEXT NOT NULL DEFAULT "",
        sort_order INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
        FOREIGN KEY (unit_id) REFERENCES units(id)
    )
');

$db->exec('
    CREATE TABLE submission_steps (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        submission_id INTEGER NOT NULL,
        step_number INTEGER NOT NULL,
        description TEXT NOT NULL,
        FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE
    )
');

echo "Tabulky vytvořeny.\n";

// ============================================================
// Číselníky – obtížnost a jednotky
// ============================================================

$difficulties = [
	['Snadné', 1],
	['Střední', 2],
	['Náročné', 3],
];

$diffStmt = $db->prepare('INSERT INTO difficulties (name, sort_order) VALUES (?, ?)');
foreach ($difficulties as $d) {
	$diffStmt->execute($d);
}

echo "Obtížnosti vloženy.\n";

$units = [
	['gram', 'g'],
	['kilogram', 'kg'],
	['mililitr', 'ml'],
	['litr', 'l'],
	['kus', 'ks'],
	['lžíce', 'lž.'],
	['lžička', 'lžič.'],
	['špetka', 'špet.'],
	['hrnek', 'hrn.'],
	['plátek', 'pl.'],
	['stroužek', 'str.'],
	['svazek', 'sv.'],
];

$unitStmt = $db->prepare('INSERT INTO units (name, abbreviation) VALUES (?, ?)');
foreach ($units as $u) {
	$unitStmt->execute($u);
}

echo "Jednotky vloženy.\n";

// ============================================================
// Vzorová data – téma: Kuchařka (recepty)
// ============================================================

// Kategorie
$categories = [
	['Polévky', 'polevky', 'assets/images/polevky.jpeg', 'Klasické české i mezinárodní polévky pro každou příležitost.'],
	['Hlavní jídla', 'hlavni-jidla', 'assets/images/hlavni-jidla.jpeg', 'Vydatná hlavní jídla z masa, ryb i zeleniny.'],
	['Těstoviny', 'testoviny', 'assets/images/testoviny.jpg', 'Těstovinové recepty od klasické bolognese po italské speciality.'],
	['Saláty', 'salaty', 'assets/images/salaty.jpeg', 'Lehké saláty jako příloha i samostatné jídlo.'],
	['Moučníky', 'moucniky', 'assets/images/dezerty.jpg', 'Dorty, koláče, buchty a další sladké pokušení.'],
	['Nápoje', 'napoje', 'assets/images/snidane.jpg', 'Domácí limonády, smoothie a teplé nápoje.'],
];

$catStmt = $db->prepare('INSERT INTO categories (name, slug, image, description) VALUES (?, ?, ?, ?)');
foreach ($categories as $cat) {
	$catStmt->execute($cat);
}

echo "Kategorie vloženy.\n";

// Recepty – [category_id, difficulty_id, name, slug, description, image, prep, cook, servings, featured]
$recipes = [
	// Polévky (category_id = 1) → id 1
	[1, 1, 'Česnečka', 'cesnecka', 'Tradiční česká česneková polévka s krutony a sýrem. Rychlá, voňavá a zahřeje vás v každém počasí.', 'assets/images/polevky.jpeg', 10, 20, 4, 1],

	// Hlavní jídla (category_id = 2) → id 2
	[2, 1, 'Kuřecí řízek', 'kureci-rizek', 'Křupavý smažený kuřecí řízek v trojobalu. Klasika, kterou má rád každý.', 'assets/images/hlavni-jidla.jpeg', 15, 20, 4, 1],

	// Těstoviny (category_id = 3) → id 3
	[3, 2, 'Špenátové gnocchi', 'spenatove-gnocchi', 'Domácí bramborové gnocchi se špenátovým pestem a smetanou.', 'assets/images/testoviny.jpg', 30, 20, 4, 1],

	// Saláty (category_id = 4) → id 4
	[4, 1, 'Řecký salát', 'recky-salat', 'Svěží salát s rajčaty, okurkou, olivami, červenou cibulí a sýrem feta.', 'assets/images/recky-salat.jpg', 15, 0, 4, 1],

	// Moučníky (category_id = 5) → id 5
	[5, 2, 'Jablečný štrúdl', 'jablecny-strudl', 'Křehké tažené těsto plněné jablky, rozinkami a skořicí. Voňavý jako u babičky.', 'assets/images/dezerty.jpg', 30, 40, 8, 1],

	// Nápoje (category_id = 6) → id 6
	[6, 1, 'Domácí limonáda', 'domaci-limonada', 'Osvěžující limonáda z citronu, máty a medu. Hotová za pět minut.', 'assets/images/snidane.jpg', 5, 0, 4, 1],
];

$recStmt = $db->prepare('
    INSERT INTO recipes (category_id, difficulty_id, name, slug, description, image, prep_time_minutes, cook_time_minutes, servings, featured)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
');

foreach ($recipes as $r) {
	$recStmt->execute($r);
}

echo "Recepty vloženy (" . count($recipes) . ").\n";

// Obrázky receptů (galerie) – ukázkově pro pár receptů
$images = [];

$imgStmt = $db->prepare('INSERT INTO recipe_images (recipe_id, image, sort_order) VALUES (?, ?, ?)');
foreach ($images as $img) {
	$imgStmt->execute($img);
}

echo "Obrázky vloženy.\n";

// Ingredience – mapování unit name → id (lazy lookup pro čitelnost)
$unitIds = [];
foreach ($db->query('SELECT id, name FROM units') as $row) {
	$unitIds[$row['name']] = (int) $row['id'];
}

// Ingredience – [recipe_id, name, amount, unit_name|null, note, sort_order]
$ingredients = [
	// Česnečka (1)
	[1, 'česnek', 4, 'stroužek', '', 1],
	[1, 'brambory', 300, 'gram', 'na kostky', 2],
	[1, 'cibule', 1, 'kus', 'najemno', 3],
	[1, 'majoránka', 1, 'lžička', 'sušená', 4],
	[1, 'voda', 1.5, 'litr', '', 5],
	[1, 'olej', 2, 'lžíce', '', 6],
	[1, 'sůl', null, null, 'podle chuti', 7],
	[1, 'tvrdý sýr', 50, 'gram', 'nastrouhaný', 8],

	// Kuřecí řízek (2)
	[2, 'kuřecí prsa', 4, 'kus', '', 1],
	[2, 'mouka hladká', 100, 'gram', '', 2],
	[2, 'vejce', 2, 'kus', '', 3],
	[2, 'strouhanka', 150, 'gram', '', 4],
	[2, 'olej na smažení', 500, 'mililitr', '', 5],
	[2, 'citron', 1, 'kus', 'na ozdobu', 6],

	// Špenátové gnocchi (3)
	[3, 'brambory', 800, 'gram', 'varné typu B', 1],
	[3, 'mouka hladká', 200, 'gram', '', 2],
	[3, 'vejce', 1, 'kus', '', 3],
	[3, 'čerstvý špenát', 200, 'gram', '', 4],
	[3, 'piniové oříšky', 30, 'gram', '', 5],
	[3, 'parmazán', 50, 'gram', '', 6],
	[3, 'smetana', 200, 'mililitr', '', 7],

	// Řecký salát (4)
	[4, 'rajčata', 4, 'kus', '', 1],
	[4, 'okurka salátová', 1, 'kus', '', 2],
	[4, 'cibule červená', 1, 'kus', '', 3],
	[4, 'olivy', 100, 'gram', 'kalamata', 4],
	[4, 'sýr feta', 200, 'gram', '', 5],
	[4, 'olivový olej', 4, 'lžíce', 'extra panenský', 6],
	[4, 'oregano', 1, 'lžička', 'sušené', 7],

	// Jablečný štrúdl (5)
	[5, 'listové těsto', 500, 'gram', '', 1],
	[5, 'jablka', 1, 'kilogram', 'kyselejší odrůdy', 2],
	[5, 'rozinky', 100, 'gram', '', 3],
	[5, 'vlašské ořechy', 80, 'gram', 'sekané', 4],
	[5, 'cukr', 100, 'gram', '', 5],
	[5, 'skořice', 2, 'lžička', 'mletá', 6],
	[5, 'máslo', 80, 'gram', 'rozpuštěné', 7],
	[5, 'strouhanka', 4, 'lžíce', '', 8],

	// Domácí limonáda (6)
	[6, 'citron', 3, 'kus', '', 1],
	[6, 'voda', 1, 'litr', 'studená', 2],
	[6, 'med', 4, 'lžíce', '', 3],
	[6, 'máta', 1, 'svazek', 'čerstvá', 4],
	[6, 'led', null, null, 'na podávání', 5],
];

$ingStmt = $db->prepare('
    INSERT INTO recipe_ingredients (recipe_id, name, amount, unit_id, note, sort_order)
    VALUES (?, ?, ?, ?, ?, ?)
');

foreach ($ingredients as [$rid, $name, $amount, $unitName, $note, $sort]) {
	$ingStmt->execute([
		$rid,
		$name,
		$amount,
		$unitName !== NULL ? $unitIds[$unitName] : NULL,
		$note,
		$sort,
	]);
}

echo "Ingredience vloženy (" . count($ingredients) . ").\n";

// Postupy – [recipe_id, step_number, description]
$steps = [
	// Česnečka (1)
	[1, 1, 'Brambory oloupejte, nakrájejte na malé kostky a uvařte ve vodě doměkka (asi 15 minut).'],
	[1, 2, 'Cibuli najemno nakrájejte a osmahněte na oleji do zlatova.'],
	[1, 3, 'Přidejte cibuli do polévky, vmačkejte česnek, ochuťte solí a majoránkou.'],
	[1, 4, 'Servírujte s opečenými krutony a posypte strouhaným sýrem.'],

	// Kuřecí řízek (2)
	[2, 1, 'Kuřecí prsa naklepejte na tloušťku 1 cm.'],
	[2, 2, 'Osolte a opepřete, obalte v mouce, rozšlehaných vejcích a strouhance.'],
	[2, 3, 'Smažte na rozpáleném oleji asi 4 minuty z každé strany do zlatova.'],
	[2, 4, 'Servírujte s plátkem citronu, vařenými bramborami nebo bramborovým salátem.'],

	// Špenátové gnocchi (3)
	[3, 1, 'Brambory uvařte ve slupce, oloupejte a prolisujte.'],
	[3, 2, 'Smíchejte s moukou a vejcem na hladké těsto. Vyválejte válečky a krájejte na noky.'],
	[3, 3, 'Špenát blanšírujte, rozmixujte s piniovými oříšky a parmazánem na pesto.'],
	[3, 4, 'Gnocchi uvařte v osolené vodě – jsou hotové, když vyplavou na hladinu.'],
	[3, 5, 'Smíchejte pesto se smetanou, přidejte gnocchi a krátce prohřejte.'],

	// Řecký salát (4)
	[4, 1, 'Rajčata a okurku nakrájejte na velké kostky.'],
	[4, 2, 'Cibuli nakrájejte na tenká kolečka.'],
	[4, 3, 'Vše smíchejte v míse, přidejte olivy a kostku fety.'],
	[4, 4, 'Pokapejte olivovým olejem, posypte oreganem a osolte.'],

	// Jablečný štrúdl (5)
	[5, 1, 'Jablka oloupejte, zbavte jádřinců a nakrájejte na drobné plátky.'],
	[5, 2, 'Smíchejte je s rozinkami, ořechy, cukrem a skořicí.'],
	[5, 3, 'Listové těsto rozválejte, potřete máslem a posypte strouhankou.'],
	[5, 4, 'Doprostřed vyskládejte jablkovou náplň a stočte do rolády.'],
	[5, 5, 'Potřete máslem a pečte v troubě při 180 °C asi 40 minut.'],
	[5, 6, 'Posypte moučkovým cukrem a podávejte mírně teplé.'],

	// Domácí limonáda (6)
	[6, 1, 'Z citronů vymačkejte šťávu.'],
	[6, 2, 'Smíchejte s vodou a medem, dobře rozmíchejte.'],
	[6, 3, 'Přidejte čerstvé lístky máty a kostky ledu. Podávejte ihned.'],
];

$stepStmt = $db->prepare('INSERT INTO recipe_steps (recipe_id, step_number, description) VALUES (?, ?, ?)');
foreach ($steps as $s) {
	$stepStmt->execute($s);
}

echo "Postupy vloženy (" . count($steps) . ").\n";

// Vzorový autor
$db->exec('
    INSERT INTO authors (name, email)
    VALUES ("Matěj Kotrba", "kotrba@kucharka.cz")
');

echo "Vzorový autor vytvořen.\n";

// Vzorové uživatelské submission (Snadné, Hlavní jídla – 1 ingredience + 1 krok pro ukázku tvaru dat)
$db->exec('
    INSERT INTO submissions (author_id, category_id, difficulty_id, name, description, prep_time_minutes, cook_time_minutes, servings, status)
    VALUES (1, 2, 1, "Míchaná vajíčka po česku", "Nadýchaná míchaná vajíčka s pažitkou a máslem.", 5, 10, 2, "pending")
');

$db->exec('
    INSERT INTO submission_ingredients (submission_id, name, amount, unit_id, note, sort_order)
    VALUES
        (1, "vejce", 4, ' . $unitIds['kus'] . ', "", 1),
        (1, "máslo", 30, ' . $unitIds['gram'] . ', "", 2),
        (1, "pažitka", 1, ' . $unitIds['svazek'] . ', "čerstvá", 3),
        (1, "sůl", NULL, NULL, "podle chuti", 4)
');

$db->exec('
    INSERT INTO submission_steps (submission_id, step_number, description)
    VALUES
        (1, 1, "Vejce rozšlehejte v misce, osolte."),
        (1, 2, "Na pánvi rozpusťte máslo a vlijte vejce."),
        (1, 3, "Stále míchejte vařečkou, dokud se vejce nesrazí."),
        (1, 4, "Posypte pažitkou a podávejte s čerstvým pečivem.")
');

echo "Vzorový recept od uživatele (submission) vytvořen.\n";

// Indexy pro rychlejší vyhledávání
$db->exec('CREATE INDEX idx_recipes_category ON recipes(category_id)');
$db->exec('CREATE INDEX idx_recipes_slug ON recipes(slug)');
$db->exec('CREATE INDEX idx_recipes_featured ON recipes(featured)');
$db->exec('CREATE INDEX idx_categories_slug ON categories(slug)');
$db->exec('CREATE INDEX idx_recipe_ingredients_recipe ON recipe_ingredients(recipe_id)');
$db->exec('CREATE INDEX idx_recipe_steps_recipe ON recipe_steps(recipe_id)');
$db->exec('CREATE INDEX idx_recipe_images_recipe ON recipe_images(recipe_id)');
$db->exec('CREATE INDEX idx_submissions_author ON submissions(author_id)');

echo "\nDatabáze úspěšně inicializována!\n";
echo "Soubor: $dbPath\n";
