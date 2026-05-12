<?php

declare(strict_types=1);

final class UnitRepository {

	private PDO $db;

	public function __construct() {
		$this->db = Database::getConnection();
	}

	/**
	 * Vrátí všechny jednotky.
	 *
	 * @return list<UnitDTO>
	 */
	public function getAll(): array {
		$stmt = $this->db->query('SELECT * FROM units ORDER BY name');

		return array_map(UnitDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Najde jednotku podle ID.
	 */
	public function getById(int $id): ?UnitDTO {
		$stmt = $this->db->prepare('SELECT * FROM units WHERE id = :id');
		$stmt->execute(['id' => $id]);

		$row = $stmt->fetch();

		return $row ? UnitDTO::fromRow($row) : NULL;
	}

}
