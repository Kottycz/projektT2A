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
	['Polévky', 'polevky', 'assets/images/kategorie/polevky.svg', 'Klasické české i mezinárodní polévky pro každou příležitost.'],
	['Hlavní jídla', 'hlavni-jidla', 'assets/images/kategorie/hlavni-jidla.svg', 'Vydatná hlavní jídla z masa, ryb i zeleniny.'],
	['Bezmasá jídla', 'bezmasa-jidla', 'assets/images/kategorie/bezmasa.svg', 'Vegetariánské recepty bohaté na chuť i barvu.'],
	['Saláty', 'salaty', 'assets/images/kategorie/salaty.svg', 'Lehké saláty jako příloha i samostatné jídlo.'],
	['Moučníky', 'moucniky', 'assets/images/kategorie/moucniky.svg', 'Dorty, koláče, buchty a další sladké pokušení.'],
	['Nápoje', 'napoje', 'assets/images/kategorie/napoje.svg', 'Domácí limonády, smoothie a teplé nápoje.'],
];

$catStmt = $db->prepare('INSERT INTO categories (name, slug, image, description) VALUES (?, ?, ?, ?)');
foreach ($categories as $cat) {
	$catStmt->execute($cat);
}

echo "Kategorie vloženy.\n";

// Recepty – [category_id, difficulty_id, name, slug, description, image, prep, cook, servings, featured]
$recipes = [
	// Polévky (category_id = 1)
	[1, 1, 'Česnečka', 'cesnecka', 'Tradiční česká česneková polévka s krutony a sýrem. Rychlá, voňavá a zahřeje vás v každém počasí.', 'assets/images/recepty/cesnecka.svg', 10, 20, 4, 1],
	[1, 2, 'Kulajda', 'kulajda', 'Sametová polévka se zakysanou smetanou, vejcem a houbami. Klenot české kuchyně.', 'assets/images/recepty/kulajda.svg', 15, 30, 4, 0],
	[1, 1, 'Bramborová polévka', 'bramboracka', 'Hustá bramboračka s kořenovou zeleninou, houbami a majoránkou.', 'assets/images/recepty/bramboracka.svg', 20, 40, 6, 1],

	// Hlavní jídla (category_id = 2)
	[2, 2, 'Svíčková na smetaně', 'svickova', 'Královna české kuchyně – hovězí svíčková se smetanovou omáčkou a houskovým knedlíkem.', 'assets/images/recepty/svickova.svg', 30, 180, 6, 1],
	[2, 1, 'Kuřecí řízek', 'kureci-rizek', 'Křupavý smažený kuřecí řízek v trojobalu. Klasika, kterou má rád každý.', 'assets/images/recepty/rizek.svg', 15, 20, 4, 1],
	[2, 3, 'Pečená kachna se zelím', 'pecena-kachna', 'Křupavá pečená kachna se dvěma druhy zelí a karlovarským knedlíkem. Nedělní oběd jak má být.', 'assets/images/recepty/kachna.svg', 30, 150, 4, 0],
	[2, 2, 'Špagety Bolognese', 'spagety-bolognese', 'Italská klasika – těstoviny s vydatnou rajčatovou omáčkou z mletého masa.', 'assets/images/recepty/bolognese.svg', 15, 60, 4, 1],
	[2, 1, 'Losos pečený na másle', 'losos-pecny', 'Šťavnatý losos s citronem, máslem a čerstvým koprem. Hotové za dvacet minut.', 'assets/images/recepty/losos.svg', 10, 20, 2, 0],

	// Bezmasá jídla (category_id = 3)
	[3, 1, 'Rizoto s houbami', 'rizoto-s-houbami', 'Krémové rizoto z hříbků, žampionů a parmazánu. Voňavé a hřejivé.', 'assets/images/recepty/rizoto.svg', 10, 35, 4, 1],
	[3, 2, 'Špenátové gnocchi', 'spenatove-gnocchi', 'Domácí bramborové gnocchi se špenátovým pestem a smetanou.', 'assets/images/recepty/gnocchi.svg', 30, 20, 4, 0],
	[3, 1, 'Pečená cuketa s fetou', 'pecena-cuketa', 'Zapečená cuketa s rajčaty, olivami a sýrem feta. Lehké letní jídlo.', 'assets/images/recepty/cuketa.svg', 15, 30, 2, 0],

	// Saláty (category_id = 4)
	[4, 1, 'Caesar salát', 'caesar-salat', 'Klasický římský salát s kuřecími nudličkami, parmazánem, krutony a domácím dresinkem.', 'assets/images/recepty/caesar.svg', 20, 10, 2, 1],
	[4, 1, 'Řecký salát', 'recky-salat', 'Svěží salát s rajčaty, okurkou, olivami, červenou cibulí a sýrem feta.', 'assets/images/recepty/recky.svg', 15, 0, 4, 0],

	// Moučníky (category_id = 5)
	[5, 2, 'Jablečný štrúdl', 'jablecny-strudl', 'Křehké tažené těsto plněné jablky, rozinkami a skořicí. Voňavý jako u babičky.', 'assets/images/recepty/strudl.svg', 30, 40, 8, 1],
	[5, 1, 'Tvarohové buchty', 'tvarohove-buchty', 'Měkoučké buchty s tvarohovou náplní. Ideální ke kávě nebo k svačině.', 'assets/images/recepty/buchty.svg', 30, 25, 12, 0],
	[5, 3, 'Čokoládový dort', 'cokoladovy-dort', 'Třípatrový čokoládový dort s ganache a malinami. Na slavnostní příležitosti.', 'assets/images/recepty/cokoladovy-dort.svg', 60, 40, 12, 1],

	// Nápoje (category_id = 6)
	[6, 1, 'Domácí limonáda', 'domaci-limonada', 'Osvěžující limonáda z citronu, máty a medu. Hotová za pět minut.', 'assets/images/recepty/limonada.svg', 5, 0, 4, 0],
	[6, 1, 'Horká čokoláda', 'horka-cokolada', 'Hustá belgická horká čokoláda s šlehačkou. Zimní klasika.', 'assets/images/recepty/cokolada.svg', 5, 10, 2, 1],
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
$images = [
	// Česnečka (id 1)
	[1, 'assets/images/recepty/cesnecka-2.svg', 1],
	[1, 'assets/images/recepty/cesnecka-3.svg', 2],

	// Svíčková (id 4)
	[4, 'assets/images/recepty/svickova-2.svg', 1],
	[4, 'assets/images/recepty/svickova-3.svg', 2],

	// Špagety bolognese (id 7)
	[7, 'assets/images/recepty/bolognese-2.svg', 1],
	[7, 'assets/images/recepty/bolognese-3.svg', 2],

	// Jablečný štrúdl (id 14)
	[14, 'assets/images/recepty/strudl-2.svg', 1],
	[14, 'assets/images/recepty/strudl-3.svg', 2],
];

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
// Pro ingredience bez množství/jednotky (např. "sůl podle chuti") nech amount=null, unit=null.
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

	// Kulajda (2)
	[2, 'brambory', 400, 'gram', 'na kostky', 1],
	[2, 'sušené houby', 30, 'gram', 'namočené', 2],
	[2, 'zakysaná smetana', 250, 'mililitr', '', 3],
	[2, 'vejce', 4, 'kus', '', 4],
	[2, 'kmín', 1, 'lžička', '', 5],
	[2, 'kopr', 1, 'svazek', 'čerstvý', 6],
	[2, 'voda', 1.5, 'litr', '', 7],

	// Bramboračka (3)
	[3, 'brambory', 500, 'gram', '', 1],
	[3, 'mrkev', 1, 'kus', '', 2],
	[3, 'celer', 100, 'gram', '', 3],
	[3, 'cibule', 1, 'kus', '', 4],
	[3, 'sušené houby', 20, 'gram', '', 5],
	[3, 'česnek', 2, 'stroužek', '', 6],
	[3, 'majoránka', 1, 'lžička', '', 7],

	// Svíčková (4)
	[4, 'hovězí svíčková', 1, 'kilogram', 'naložená', 1],
	[4, 'kořenová zelenina', 500, 'gram', 'mrkev, petržel, celer', 2],
	[4, 'cibule', 2, 'kus', '', 3],
	[4, 'smetana ke šlehání', 250, 'mililitr', '', 4],
	[4, 'slanina', 100, 'gram', '', 5],
	[4, 'cukr', 2, 'lžíce', '', 6],
	[4, 'ocet', 50, 'mililitr', '', 7],
	[4, 'bobkový list', 3, 'kus', '', 8],
	[4, 'nové koření', 5, 'kus', '', 9],

	// Kuřecí řízek (5)
	[5, 'kuřecí prsa', 4, 'kus', '', 1],
	[5, 'mouka hladká', 100, 'gram', '', 2],
	[5, 'vejce', 2, 'kus', '', 3],
	[5, 'strouhanka', 150, 'gram', '', 4],
	[5, 'olej na smažení', 500, 'mililitr', '', 5],
	[5, 'citron', 1, 'kus', 'na ozdobu', 6],

	// Pečená kachna (6)
	[6, 'celá kachna', 1, 'kus', 'asi 2 kg', 1],
	[6, 'kmín', 2, 'lžíce', '', 2],
	[6, 'česnek', 4, 'stroužek', '', 3],
	[6, 'kysané zelí', 500, 'gram', '', 4],
	[6, 'cibule', 1, 'kus', '', 5],
	[6, 'sůl', null, null, 'na vetření', 6],

	// Bolognese (7)
	[7, 'špagety', 500, 'gram', '', 1],
	[7, 'mleté hovězí', 500, 'gram', '', 2],
	[7, 'rajčatový protlak', 2, 'lžíce', '', 3],
	[7, 'loupaná rajčata', 400, 'gram', 'konzerva', 4],
	[7, 'cibule', 1, 'kus', '', 5],
	[7, 'česnek', 3, 'stroužek', '', 6],
	[7, 'oregano', 1, 'lžička', 'sušené', 7],
	[7, 'bazalka', 1, 'svazek', 'čerstvá', 8],
	[7, 'parmazán', 50, 'gram', 'na podávání', 9],

	// Losos (8)
	[8, 'filé z lososa', 2, 'kus', 'asi 150 g každé', 1],
	[8, 'máslo', 50, 'gram', '', 2],
	[8, 'citron', 1, 'kus', '', 3],
	[8, 'kopr', 1, 'svazek', 'čerstvý', 4],

	// Rizoto s houbami (9)
	[9, 'rýže Arborio', 300, 'gram', '', 1],
	[9, 'směs hub', 400, 'gram', 'hříbky, žampiony', 2],
	[9, 'cibule', 1, 'kus', 'najemno', 3],
	[9, 'bílé víno', 100, 'mililitr', 'suché', 4],
	[9, 'zeleninový vývar', 1, 'litr', '', 5],
	[9, 'parmazán', 80, 'gram', 'strouhaný', 6],
	[9, 'máslo', 30, 'gram', '', 7],

	// Špenátové gnocchi (10)
	[10, 'brambory', 800, 'gram', 'varné typu B', 1],
	[10, 'mouka hladká', 200, 'gram', '', 2],
	[10, 'vejce', 1, 'kus', '', 3],
	[10, 'čerstvý špenát', 200, 'gram', '', 4],
	[10, 'piniové oříšky', 30, 'gram', '', 5],
	[10, 'parmazán', 50, 'gram', '', 6],
	[10, 'smetana', 200, 'mililitr', '', 7],

	// Pečená cuketa (11)
	[11, 'cuketa', 2, 'kus', '', 1],
	[11, 'cherry rajčata', 200, 'gram', '', 2],
	[11, 'sýr feta', 150, 'gram', '', 3],
	[11, 'olivy černé', 50, 'gram', '', 4],
	[11, 'olivový olej', 3, 'lžíce', '', 5],
	[11, 'tymián', null, null, 'snítka', 6],

	// Caesar (12)
	[12, 'římský salát', 1, 'kus', '', 1],
	[12, 'kuřecí prsa', 300, 'gram', '', 2],
	[12, 'parmazán', 50, 'gram', 'strouhaný + hoblinky', 3],
	[12, 'krutony', 1, 'hrnek', '', 4],
	[12, 'majonéza', 4, 'lžíce', '', 5],
	[12, 'ančovičky', 4, 'kus', '', 6],
	[12, 'česnek', 1, 'stroužek', '', 7],
	[12, 'citron', 0.5, 'kus', 'šťáva', 8],

	// Řecký (13)
	[13, 'rajčata', 4, 'kus', '', 1],
	[13, 'okurka salátová', 1, 'kus', '', 2],
	[13, 'cibule červená', 1, 'kus', '', 3],
	[13, 'olivy', 100, 'gram', 'kalamata', 4],
	[13, 'sýr feta', 200, 'gram', '', 5],
	[13, 'olivový olej', 4, 'lžíce', 'extra panenský', 6],
	[13, 'oregano', 1, 'lžička', 'sušené', 7],

	// Jablečný štrúdl (14)
	[14, 'listové těsto', 500, 'gram', '', 1],
	[14, 'jablka', 1, 'kilogram', 'kyselejší odrůdy', 2],
	[14, 'rozinky', 100, 'gram', '', 3],
	[14, 'vlašské ořechy', 80, 'gram', 'sekané', 4],
	[14, 'cukr', 100, 'gram', '', 5],
	[14, 'skořice', 2, 'lžička', 'mletá', 6],
	[14, 'máslo', 80, 'gram', 'rozpuštěné', 7],
	[14, 'strouhanka', 4, 'lžíce', '', 8],

	// Tvarohové buchty (15)
	[15, 'mouka hladká', 500, 'gram', '', 1],
	[15, 'mléko', 250, 'mililitr', 'vlažné', 2],
	[15, 'droždí', 30, 'gram', 'čerstvé', 3],
	[15, 'cukr', 80, 'gram', '', 4],
	[15, 'vejce', 2, 'kus', '', 5],
	[15, 'máslo', 80, 'gram', 'rozpuštěné', 6],
	[15, 'tvaroh', 500, 'gram', 'tučný', 7],
	[15, 'vanilkový cukr', 1, 'lžíce', '', 8],

	// Čokoládový dort (16)
	[16, 'mouka hladká', 300, 'gram', '', 1],
	[16, 'cukr', 250, 'gram', '', 2],
	[16, 'kakao', 80, 'gram', '', 3],
	[16, 'vejce', 4, 'kus', '', 4],
	[16, 'mléko', 250, 'mililitr', '', 5],
	[16, 'olej', 150, 'mililitr', '', 6],
	[16, 'prášek do pečiva', 1, 'lžíce', '', 7],
	[16, 'tmavá čokoláda', 200, 'gram', 'na ganache', 8],
	[16, 'smetana ke šlehání', 200, 'mililitr', 'na ganache', 9],
	[16, 'maliny', 200, 'gram', 'čerstvé', 10],

	// Domácí limonáda (17)
	[17, 'citron', 3, 'kus', '', 1],
	[17, 'voda', 1, 'litr', 'studená', 2],
	[17, 'med', 4, 'lžíce', '', 3],
	[17, 'máta', 1, 'svazek', 'čerstvá', 4],
	[17, 'led', null, null, 'na podávání', 5],

	// Horká čokoláda (18)
	[18, 'mléko', 500, 'mililitr', 'plnotučné', 1],
	[18, 'tmavá čokoláda', 100, 'gram', '70 %', 2],
	[18, 'cukr', 1, 'lžíce', '', 3],
	[18, 'šlehačka', 100, 'mililitr', 'vyšlehaná', 4],
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

	// Kulajda (2)
	[2, 1, 'Sušené houby namočte na 30 minut do teplé vody.'],
	[2, 2, 'Brambory nakrájejte na kostky a uvařte ve vodě s kmínem a houbami.'],
	[2, 3, 'Smetanu rozšlehejte s trochou mouky a polévku jí zahustěte.'],
	[2, 4, 'Vejce uvařte naměkko nebo natvrdo, rozkrojte a vložte do talíře.'],
	[2, 5, 'Polévku posypte čerstvým koprem a podávejte.'],

	// Bramboračka (3)
	[3, 1, 'Sušené houby namočte do teplé vody na 30 minut.'],
	[3, 2, 'Cibuli osmahněte, přidejte nakrájenou kořenovou zeleninu a krátce orestujte.'],
	[3, 3, 'Vsypte brambory, zalijte vodou a vařte 20 minut.'],
	[3, 4, 'Přidejte houby i s vodou, kořenění a vařte ještě 10 minut.'],
	[3, 5, 'Polévku zahustěte jíškou a dochuťte česnekem a majoránkou.'],

	// Svíčková (4)
	[4, 1, 'Maso prošpikujte slaninou, osolte a opečte ze všech stran.'],
	[4, 2, 'V kastrolu rozpusťte máslo, přidejte cibuli a zeleninu, krátce restujte.'],
	[4, 3, 'Zalijte vývarem, přidejte koření a maso. Pečte v troubě při 160 °C 2–3 hodiny.'],
	[4, 4, 'Maso vyjměte, zeleninu rozmixujte do hladkého omáčky.'],
	[4, 5, 'Omáčku zjemněte smetanou, dochuťte cukrem a octem.'],
	[4, 6, 'Podávejte s knedlíkem, brusinkami a šlehačkou.'],

	// Kuřecí řízek (5)
	[5, 1, 'Kuřecí prsa naklepejte na tloušťku 1 cm.'],
	[5, 2, 'Osolte a opepřete, obalte v mouce, rozšlehaných vejcích a strouhance.'],
	[5, 3, 'Smažte na rozpáleném oleji asi 4 minuty z každé strany do zlatova.'],
	[5, 4, 'Servírujte s plátkem citronu, vařenými bramborami nebo bramborovým salátem.'],

	// Pečená kachna (6)
	[6, 1, 'Kachnu omyjte, osušte a po celém povrchu nasolte. Vetřete kmín a česnek.'],
	[6, 2, 'Vložte do pekáče prsy dolů, podlijte vodou a pečte při 160 °C 90 minut.'],
	[6, 3, 'Otočte na druhou stranu a pečte dalších 45 minut, podlévejte vlastní šťávou.'],
	[6, 4, 'Mezitím dušte na cibuli zelí s trochou vody a kořením.'],
	[6, 5, 'Kachnu nakrájejte a podávejte se zelím a knedlíkem.'],

	// Bolognese (7)
	[7, 1, 'Cibuli a česnek najemno nakrájejte a osmahněte na oleji.'],
	[7, 2, 'Přidejte mleté maso a opékejte, dokud nezhnědne.'],
	[7, 3, 'Vmíchejte protlak, loupaná rajčata a koření. Duste na mírném plameni 30–40 minut.'],
	[7, 4, 'Špagety uvařte al dente podle návodu na obalu.'],
	[7, 5, 'Servírujte s omáčkou, posypte parmazánem a bazalkou.'],

	// Losos (8)
	[8, 1, 'Filé z lososa osušte papírovým ubrouskem, osolte a opepřete.'],
	[8, 2, 'Na pánvi rozpusťte máslo, přidejte plátky citronu.'],
	[8, 3, 'Vložte lososa kůží dolů a pečte 4 minuty, otočte a pečte další 3 minuty.'],
	[8, 4, 'Posypte čerstvým koprem a podávejte s vařenými brambory nebo salátem.'],

	// Rizoto (9)
	[9, 1, 'Houby očistěte, větší nakrájejte. Cibuli najemno nakrájejte.'],
	[9, 2, 'Na másle osmahněte cibuli, přidejte rýži a krátce restujte.'],
	[9, 3, 'Zalijte vínem a nechte odpařit. Postupně přidávejte horký vývar po naběračkách.'],
	[9, 4, 'Po polovině doby přidejte houby a vařte do měkka rýže (asi 20 minut).'],
	[9, 5, 'Vmíchejte parmazán a kostku másla, dochuťte solí a pepřem.'],

	// Gnocchi (10)
	[10, 1, 'Brambory uvařte ve slupce, oloupejte a prolisujte.'],
	[10, 2, 'Smíchejte s moukou a vejcem na hladké těsto. Vyválejte válečky a krájejte na noky.'],
	[10, 3, 'Špenát blanšírujte, rozmixujte s piniovými oříšky a parmazánem na pesto.'],
	[10, 4, 'Gnocchi uvařte v osolené vodě – jsou hotové, když vyplavou na hladinu.'],
	[10, 5, 'Smíchejte pesto se smetanou, přidejte gnocchi a krátce prohřejte.'],

	// Pečená cuketa (11)
	[11, 1, 'Cuketu nakrájejte na plátky asi 1 cm silné.'],
	[11, 2, 'Naskládejte do pekáčku, prokládejte cherry rajčaty a olivami.'],
	[11, 3, 'Posypte rozdrobenou fetou, pokapejte olivovým olejem a osypte tymiánem.'],
	[11, 4, 'Pečte v troubě při 200 °C asi 25 minut, dokud cuketa nezměkne.'],

	// Caesar (12)
	[12, 1, 'Kuřecí prsa osolte, opepřete a opečte na pánvi. Nakrájejte na proužky.'],
	[12, 2, 'Salát natrhejte na sousta a vložte do mísy.'],
	[12, 3, 'Rozmixujte majonézu, ančovičky, česnek, citronovou šťávu a polovinu parmazánu na dresink.'],
	[12, 4, 'Salát zalijte dresinkem, přidejte krutony, kuře a hoblinky parmazánu.'],

	// Řecký (13)
	[13, 1, 'Rajčata a okurku nakrájejte na velké kostky.'],
	[13, 2, 'Cibuli nakrájejte na tenká kolečka.'],
	[13, 3, 'Vše smíchejte v míse, přidejte olivy a kostku fety.'],
	[13, 4, 'Pokapejte olivovým olejem, posypte oreganem a osolte.'],

	// Štrúdl (14)
	[14, 1, 'Jablka oloupejte, zbavte jádřinců a nakrájejte na drobné plátky.'],
	[14, 2, 'Smíchejte je s rozinkami, ořechy, cukrem a skořicí.'],
	[14, 3, 'Listové těsto rozválejte, potřete máslem a posypte strouhankou.'],
	[14, 4, 'Doprostřed vyskládejte jablkovou náplň a stočte do rolády.'],
	[14, 5, 'Potřete máslem a pečte v troubě při 180 °C asi 40 minut.'],
	[14, 6, 'Posypte moučkovým cukrem a podávejte mírně teplé.'],

	// Tvarohové buchty (15)
	[15, 1, 'Z mléka, droždí, cukru a lžíce mouky připravte kvásek a nechte vzejít.'],
	[15, 2, 'Smíchejte mouku, vejce, máslo a kvásek a vypracujte hladké těsto. Nechte kynout 1 hodinu.'],
	[15, 3, 'Tvaroh utřete s cukrem a vanilkovým cukrem.'],
	[15, 4, 'Z těsta vykrájejte kolečka, plňte tvarohem a zformujte buchty.'],
	[15, 5, 'Pokládejte do vymazaného plechu, nechte ještě 20 minut kynout.'],
	[15, 6, 'Pečte při 180 °C asi 25 minut do zlatova.'],

	// Čokoládový dort (16)
	[16, 1, 'Smíchejte suché ingredience – mouku, cukr, kakao a prášek do pečiva.'],
	[16, 2, 'V druhé míse rozšlehejte vejce, mléko a olej. Suroviny propojte.'],
	[16, 3, 'Těsto rozdělte do tří dortových forem (20 cm) a pečte při 175 °C 25–30 minut.'],
	[16, 4, 'Smetanu přiveďte k varu, zalijte čokoládu a míchejte, dokud nevznikne hladká ganache.'],
	[16, 5, 'Vychladlé korpusy plňte ganache, prokládejte malinami a obložte zbytkem ganache.'],
	[16, 6, 'Před servírováním dort nechte vychladit alespoň 2 hodiny.'],

	// Limonáda (17)
	[17, 1, 'Z citronů vymačkejte šťávu.'],
	[17, 2, 'Smíchejte s vodou a medem, dobře rozmíchejte.'],
	[17, 3, 'Přidejte čerstvé lístky máty a kostky ledu. Podávejte ihned.'],

	// Horká čokoláda (18)
	[18, 1, 'Mléko ohřejte v hrnci s cukrem (nesmí se vařit).'],
	[18, 2, 'Stáhněte z plotny, přidejte nalámanou čokoládu a metličkou rozpusťte.'],
	[18, 3, 'Nalijte do hrnků, ozdobte šlehačkou a posypte kakaem.'],
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
