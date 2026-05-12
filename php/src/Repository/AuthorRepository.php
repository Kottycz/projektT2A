<?php

declare(strict_types=1);

final class AuthorRepository {

	private PDO $db;

	public function __construct() {
		$this->db = Database::getConnection();
	}

	/**
	 * Najde autora podle ID.
	 */
	public function getById(int $id): ?AuthorDTO {
		$stmt = $this->db->prepare('SELECT * FROM authors WHERE id = :id');
		$stmt->execute(['id' => $id]);

		$row = $stmt->fetch();

		return $row ? AuthorDTO::fromRow($row) : NULL;
	}

	/**
	 * Najde autora podle e-mailu.
	 */
	public function getByEmail(string $email): ?AuthorDTO {
		$stmt = $this->db->prepare('SELECT * FROM authors WHERE email = :email');
		$stmt->execute(['email' => $email]);

		$row = $stmt->fetch();

		return $row ? AuthorDTO::fromRow($row) : NULL;
	}

	/**
	 * Vytvoří nového autora a vrátí jeho DTO.
	 */
	public function create(string $name, string $email): AuthorDTO {
		$stmt = $this->db->prepare('
			INSERT INTO authors (name, email)
			VALUES (:name, :email)
		');

		$stmt->execute([
			'name'  => $name,
			'email' => $email,
		]);

		return $this->getById((int) $this->db->lastInsertId())
			?? throw new \RuntimeException('Nepodařilo se vytvořit autora.');
	}

}
