# Kottyho kuchařka

Webová kuchařka s recepty, kategoriemi, vyhledáváním, oblíbenými recepty a plnou správou receptů (přidání, úprava, smazání). Projekt je postavený na **PHP 8.3**, **SQLite** databázi, sessions a prepared statements.

---

## Spuštění

Spouštěj z kořene složky `php/`:

```bash
# 1. Inicializace databáze (vytvoří tabulky a vzorová data)
php database/init.php

# 2. Spuštění vývojového serveru
php -S localhost:8080 -t .
```

Otevři v prohlížeči: **http://localhost:8080/index.php**

### Reset databáze

```bash
php database/init.php
```

> Smaže stávající `kucharka.db` a vytvoří novou s vzorovými daty.

---

## Struktura projektu

```
php/
├── index.php                  ← hlavní stránka
├── recepty.php                ← výpis všech receptů
├── recept.php                 ← detail receptu
├── kategorie.php              ← recepty v kategorii
├── kategorie-receptu.php      ← přehled všech kategorií
├── vyhledavani.php            ← výsledky vyhledávání
├── oblibene.php               ← oblíbené recepty
├── oblibene-potvrzeni.php     ← potvrzení přidání do oblíbených
├── pridat-recept.php          ← formulář pro přidání receptu
├── upravit-recept.php         ← formulář pro úpravu receptu
├── smazat-recept.php          ← potvrzení smazání receptu
├── recept-pridan.php          ← potvrzení po přidání receptu
├── kontakt.php                ← kontaktní formulář s validací
├── o-nas.php                  ← stránka o projektu
├── 404.php                    ← stránka pro nenalezené zdroje
│
├── partials/
│   ├── header.php             ← hlavička s navigací a počitadlem oblíbených
│   ├── footer.php             ← patička
│   └── recipe-card.php        ← znovupoužitelná karta receptu
│
├── src/
│   ├── bootstrap.php          ← načte všechny třídy
│   ├── Database.php           ← připojení k SQLite
│   ├── Favorites.php          ← oblíbené recepty v session
│   ├── Validator.php          ← validátor formulářů (fluent interface)
│   ├── DTO/                   ← readonly datové objekty
│   └── Repository/            ← třídy pro práci s databází
│
├── database/
│   ├── init.php               ← vytvoření a naplnění databáze
│   └── kucharka.db            ← SQLite databáze (generuje se přes init.php)
│
└── assets/
    ├── css/                   ← styly (main.css + dílčí soubory)
    └── images/                ← obrázky receptů, kategorií, pozadí, logo
```

---

## Přehled stránek

| Stránka | URL |
|---|---|
| Hlavní stránka | `index.php` |
| Všechny recepty | `recepty.php` |
| Detail receptu | `recept.php?slug=...` |
| Recepty v kategorii | `kategorie.php?slug=...` |
| Přehled kategorií | `kategorie-receptu.php` |
| Vyhledávání | `vyhledavani.php?q=...` |
| Oblíbené recepty | `oblibene.php` |
| Přidat recept | `pridat-recept.php` |
| Upravit recept | `upravit-recept.php?slug=...` |
| Smazat recept | `smazat-recept.php?slug=...` |
| Kontakt | `kontakt.php` |
| O nás | `o-nas.php` |
| 404 | `404.php` |

---

## Databázové tabulky

| Tabulka | Účel |
|---|---|
| `categories` | Kategorie receptů (název, slug, obrázek, popis) |
| `difficulties` | Číselník obtížnosti (Snadné / Střední / Náročné) |
| `units` | Měrné jednotky (g, kg, ml, l, ks, lžíce…) |
| `recipes` | Recepty (název, slug, popis, časy, porce, obrázek, příznak doporučený) |
| `recipe_images` | Galerie dalších obrázků receptu |
| `recipe_ingredients` | Suroviny receptu s množstvím a jednotkou |
| `recipe_steps` | Kroky postupu seřazené podle `step_number` |
| `authors` | Autoři uživatelsky zaslaných receptů |
| `submissions` | Uživatelem zaslané recepty čekající na schválení |
| `submission_ingredients` | Suroviny zaslaného receptu |
| `submission_steps` | Kroky postupu zaslaného receptu |

---

## Bezpečnost

Projekt používá CSRF tokeny u všech POST formulářů, `htmlspecialchars()` u každého výpisu, prepared statements pro všechny SQL dotazy, server-side validaci přes třídu `Validator` a `declare(strict_types=1)` v každém PHP souboru.
