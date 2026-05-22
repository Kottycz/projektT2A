<?php

declare(strict_types=1);

final class RecipeRepository
{

	private PDO $db;

	/**
	 * Společný SELECT pro všechny dotazy na recepty.
	 * Obsahuje JOIN na kategorie a obtížnosti, takže DTO dostane i jejich názvy.
	 */
	private const BASE_SELECT = '
		SELECT r.*,
			c.name AS category_name,
			c.slug AS category_slug,
			d.name AS difficulty_name
		FROM recipes r
		JOIN categories c ON r.category_id = c.id
		JOIN difficulties d ON r.difficulty_id = d.id
	';

	public function __construct()
	{
		$this->db = Database::getConnection();
	}

	/**
	 * Vrátí všechny recepty.
	 *
	 * @return list<RecipeDTO>
	 */
	public function getAll(): array
	{
		$stmt = $this->db->query(self::BASE_SELECT . ' ORDER BY r.created_at DESC');

		return array_map(RecipeDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Najde recept podle ID.
	 */
	public function getById(int $id): ?RecipeDTO
	{
		$stmt = $this->db->prepare(self::BASE_SELECT . ' WHERE r.id = :id');
		$stmt->execute(['id' => $id]);

		$row = $stmt->fetch();

		return $row ? RecipeDTO::fromRow($row) : NULL;
	}

	/**
	 * Najde recept podle slugu.
	 */
	public function getBySlug(string $slug): ?RecipeDTO
	{
		$stmt = $this->db->prepare(self::BASE_SELECT . ' WHERE r.slug = :slug');
		$stmt->execute(['slug' => $slug]);

		$row = $stmt->fetch();

		return $row ? RecipeDTO::fromRow($row) : NULL;
	}

	/**
	 * Vrátí recepty v dané kategorii.
	 *
	 * @return list<RecipeDTO>
	 */
	public function getByCategory(int $categoryId): array
	{
		$stmt = $this->db->prepare(self::BASE_SELECT . '
			WHERE r.category_id = :categoryId
			ORDER BY r.created_at DESC
		');
		$stmt->execute(['categoryId' => $categoryId]);

		return array_map(RecipeDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Vrátí recepty v kategorii podle slugu kategorie.
	 *
	 * @return list<RecipeDTO>
	 */
	public function getByCategorySlug(string $slug): array
	{
		$stmt = $this->db->prepare(self::BASE_SELECT . '
			WHERE c.slug = :slug
			ORDER BY r.created_at DESC
		');
		$stmt->execute(['slug' => $slug]);

		return array_map(RecipeDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Vrátí doporučené (featured) recepty pro hlavní stránku.
	 *
	 * @return list<RecipeDTO>
	 */
	public function getFeatured(int $limit = 6): array
	{
		$stmt = $this->db->prepare(self::BASE_SELECT . '
			WHERE r.featured = 1
			ORDER BY r.created_at DESC
			LIMIT :limit
		');
		$stmt->bindValue('limit', $limit, PDO::PARAM_INT);
		$stmt->execute();

		return array_map(RecipeDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Vrátí náhodně vybrané recepty — výběr se mění každou hodinu.
	 *
	 * @return list<RecipeDTO>
	 */
	public function getFeaturedHourly(int $limit = 6): array
	{
		$all = $this->getAll();
		mt_srand((int) date('YmdH'));
		shuffle($all);
		return array_slice($all, 0, $limit);
	}

	/**
	 * Vrátí konkrétní recepty podle pole ID (pro stránku oblíbených).
	 *
	 * @param list<int> $ids
	 * @return list<RecipeDTO>
	 */
	public function getByIds(array $ids): array
	{
		if ($ids === []) {
			return [];
		}

		$placeholders = implode(',', array_fill(0, count($ids), '?'));
		$stmt = $this->db->prepare(self::BASE_SELECT . "
			WHERE r.id IN ($placeholders)
			ORDER BY r.name
		");
		$stmt->execute(array_values($ids));

		return array_map(RecipeDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Vyhledá recepty podle názvu, popisu nebo názvu ingredience.
	 *
	 * @return list<RecipeDTO>
	 */
	public function search(string $query): array
	{
		$escaped = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $query);
		$like = '%' . $escaped . '%';

		$stmt = $this->db->prepare(self::BASE_SELECT . "
			WHERE r.name LIKE :q ESCAPE '\\'
				OR r.description LIKE :q ESCAPE '\\'
				OR EXISTS (
					SELECT 1 FROM recipe_ingredients i
					WHERE i.recipe_id = r.id AND i.name LIKE :q ESCAPE '\\'
				)
			ORDER BY r.name
		");
		$stmt->execute(['q' => $like]);

		return array_map(RecipeDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Vrátí obrázky galerie pro daný recept.
	 *
	 * @return list<RecipeImageDTO>
	 */
	public function getImages(int $recipeId): array
	{
		$stmt = $this->db->prepare('
			SELECT * FROM recipe_images
			WHERE recipe_id = :recipeId
			ORDER BY sort_order
		');
		$stmt->execute(['recipeId' => $recipeId]);

		return array_map(RecipeImageDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Vrátí ingredience daného receptu (s názvem a zkratkou jednotky).
	 *
	 * @return list<IngredientDTO>
	 */
	public function getIngredients(int $recipeId): array
	{
		$stmt = $this->db->prepare('
			SELECT i.*, u.name AS unit_name, u.abbreviation AS unit_abbreviation
			FROM recipe_ingredients i
			LEFT JOIN units u ON i.unit_id = u.id
			WHERE i.recipe_id = :recipeId
			ORDER BY i.sort_order, i.id
		');
		$stmt->execute(['recipeId' => $recipeId]);

		return array_map(IngredientDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Vytvoří nový recept a vrátí jeho slug.
	 */
	public function create(
		int $categoryId,
		int $difficultyId,
		string $name,
		string $description,
		string $image,
		int $prepTimeMinutes,
		int $cookTimeMinutes,
		int $servings,
	): string {
		$slug = $this->generateUniqueSlug($name);

		$stmt = $this->db->prepare('
			INSERT INTO recipes
				(category_id, difficulty_id, name, slug, description, image, prep_time_minutes, cook_time_minutes, servings, featured)
			VALUES
				(:categoryId, :difficultyId, :name, :slug, :description, :image, :prepTime, :cookTime, :servings, 0)
		');
		$stmt->execute([
			'categoryId'   => $categoryId,
			'difficultyId' => $difficultyId,
			'name'         => $name,
			'slug'         => $slug,
			'description'  => $description,
			'image'        => $image,
			'prepTime'     => $prepTimeMinutes,
			'cookTime'     => $cookTimeMinutes,
			'servings'     => $servings,
		]);

		return $slug;
	}

	private function generateUniqueSlug(string $name): string
	{
		$base = $this->slugify($name);
		$slug = $base;
		$i    = 2;
		while ($this->slugExists($slug)) {
			$slug = $base . '-' . $i;
			$i++;
		}
		return $slug;
	}

	private function slugify(string $text): string
	{
		$map  = ['á'=>'a','č'=>'c','ď'=>'d','é'=>'e','ě'=>'e','í'=>'i','ň'=>'n','ó'=>'o','ř'=>'r','š'=>'s','ť'=>'t','ú'=>'u','ů'=>'u','ý'=>'y','ž'=>'z'];
		$text = strtr(mb_strtolower($text, 'UTF-8'), $map);
		$text = (string) preg_replace('/[^a-z0-9]+/', '-', $text);
		return trim($text, '-');
	}

	private function slugExists(string $slug): bool
	{
		$stmt = $this->db->prepare('SELECT 1 FROM recipes WHERE slug = :slug');
		$stmt->execute(['slug' => $slug]);
		return $stmt->fetchColumn() !== false;
	}

	/**
	 * Aktualizuje základní údaje receptu.
	 */
	public function update(
		int $id,
		int $categoryId,
		int $difficultyId,
		string $name,
		string $description,
		string $image,
		int $prepTimeMinutes,
		int $cookTimeMinutes,
		int $servings,
	): void {
		$stmt = $this->db->prepare('
			UPDATE recipes SET
				category_id = :categoryId,
				difficulty_id = :difficultyId,
				name = :name,
				description = :description,
				image = :image,
				prep_time_minutes = :prepTime,
				cook_time_minutes = :cookTime,
				servings = :servings
			WHERE id = :id
		');
		$stmt->execute([
			'id'           => $id,
			'categoryId'   => $categoryId,
			'difficultyId' => $difficultyId,
			'name'         => $name,
			'description'  => $description,
			'image'        => $image,
			'prepTime'     => $prepTimeMinutes,
			'cookTime'     => $cookTimeMinutes,
			'servings'     => $servings,
		]);
	}

	/**
	 * Smaže všechny ingredience receptu a vloží nové.
	 *
	 * @param list<string> $lines  Každý řádek = jedna ingredience
	 */
	public function replaceIngredients(int $recipeId, array $lines): void
	{
		$this->db->prepare('DELETE FROM recipe_ingredients WHERE recipe_id = :id')
			->execute(['id' => $recipeId]);

		$stmt = $this->db->prepare('
			INSERT INTO recipe_ingredients (recipe_id, name, sort_order)
			VALUES (:recipeId, :name, :sortOrder)
		');
		foreach ($lines as $i => $line) {
			$stmt->execute(['recipeId' => $recipeId, 'name' => $line, 'sortOrder' => $i + 1]);
		}
	}

	/**
	 * Smaže všechny kroky receptu a vloží nové.
	 *
	 * @param list<string> $lines  Každý řádek = jeden krok
	 */
	public function replaceSteps(int $recipeId, array $lines): void
	{
		$this->db->prepare('DELETE FROM recipe_steps WHERE recipe_id = :id')
			->execute(['id' => $recipeId]);

		$stmt = $this->db->prepare('
			INSERT INTO recipe_steps (recipe_id, step_number, description)
			VALUES (:recipeId, :stepNumber, :description)
		');
		foreach ($lines as $i => $line) {
			$stmt->execute(['recipeId' => $recipeId, 'stepNumber' => $i + 1, 'description' => $line]);
		}
	}

	/**
	 * Smaže recept (ingredience, kroky a obrázky se smažou automaticky přes CASCADE).
	 */
	public function delete(int $id): void
	{
		$this->db->prepare('DELETE FROM recipes WHERE id = :id')
			->execute(['id' => $id]);
	}

	/**
	 * Vrátí kroky postupu daného receptu seřazené podle čísla kroku.
	 *
	 * @return list<RecipeStepDTO>
	 */
	public function getSteps(int $recipeId): array
	{
		$stmt = $this->db->prepare('
			SELECT * FROM recipe_steps
			WHERE recipe_id = :recipeId
			ORDER BY step_number
		');
		$stmt->execute(['recipeId' => $recipeId]);

		return array_map(RecipeStepDTO::fromRow(...), $stmt->fetchAll());
	}
}
