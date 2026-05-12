<?php

declare(strict_types=1);

/**
 * Bootstrap – načte všechny třídy projektu.
 *
 * Na začátku každé PHP stránky stačí vložit:
 *   require_once __DIR__ . '/../src/bootstrap.php';
 */

// Database
require_once __DIR__ . '/Database.php';

// DTO
require_once __DIR__ . '/DTO/CategoryDTO.php';
require_once __DIR__ . '/DTO/DifficultyDTO.php';
require_once __DIR__ . '/DTO/UnitDTO.php';
require_once __DIR__ . '/DTO/RecipeDTO.php';
require_once __DIR__ . '/DTO/RecipeImageDTO.php';
require_once __DIR__ . '/DTO/IngredientDTO.php';
require_once __DIR__ . '/DTO/RecipeStepDTO.php';
require_once __DIR__ . '/DTO/AuthorDTO.php';
require_once __DIR__ . '/DTO/SubmissionDTO.php';
require_once __DIR__ . '/DTO/SubmissionIngredientDTO.php';
require_once __DIR__ . '/DTO/SubmissionStepDTO.php';

// Repositories
require_once __DIR__ . '/Repository/CategoryRepository.php';
require_once __DIR__ . '/Repository/DifficultyRepository.php';
require_once __DIR__ . '/Repository/UnitRepository.php';
require_once __DIR__ . '/Repository/RecipeRepository.php';
require_once __DIR__ . '/Repository/AuthorRepository.php';
require_once __DIR__ . '/Repository/SubmissionRepository.php';

// Favorites (session – obdoba košíku)
require_once __DIR__ . '/Favorites.php';

// Validator
require_once __DIR__ . '/Validator.php';
