<?php

declare(strict_types=1);

final class CategoryRepository {

	private PDO $db;

	public function __construct() {
		$this->db = Database::getConnection();
	}

	/**
	 * Vrátí všechny kategorie.
	 *
	 * @return list<CategoryDTO>
	 */
	public function getAll(): array {
		$stmt = $this->db->query('SELECT * FROM categories ORDER BY name');

		return array_map(
			CategoryDTO::fromRow(...),
			$stmt->fetchAll(),
		);
	}

	/**
	 * Najde kategorii podle ID.
	 */
	public function getById(int $id): ?CategoryDTO {
		$stmt = $this->db->prepare('SELECT * FROM categories WHERE id = :id');
		$stmt->execute(['id' => $id]);

		$row = $stmt->fetch();

		return $row ? CategoryDTO::fromRow($row) : NULL;
	}

	/**
	 * Najde kategorii podle slugu (URL identifikátoru).
	 */
	public function getBySlug(string $slug): ?CategoryDTO {
		$stmt = $this->db->prepare('SELECT * FROM categories WHERE slug = :slug');
		$stmt->execute(['slug' => $slug]);

		$row = $stmt->fetch();

		return $row ? CategoryDTO::fromRow($row) : NULL;
	}

}
