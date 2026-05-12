<?php

declare(strict_types=1);

final class DifficultyRepository {

	private PDO $db;

	public function __construct() {
		$this->db = Database::getConnection();
	}

	/**
	 * Vrátí všechny obtížnosti seřazené od nejsnazší.
	 *
	 * @return list<DifficultyDTO>
	 */
	public function getAll(): array {
		$stmt = $this->db->query('SELECT * FROM difficulties ORDER BY sort_order');

		return array_map(DifficultyDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Najde obtížnost podle ID.
	 */
	public function getById(int $id): ?DifficultyDTO {
		$stmt = $this->db->prepare('SELECT * FROM difficulties WHERE id = :id');
		$stmt->execute(['id' => $id]);

		$row = $stmt->fetch();

		return $row ? DifficultyDTO::fromRow($row) : NULL;
	}

}
