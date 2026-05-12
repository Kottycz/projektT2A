<?php

declare(strict_types=1);

final class SubmissionRepository {

	private PDO $db;

	public function __construct() {
		$this->db = Database::getConnection();
	}

	/**
	 * Najde uživatelem zaslaný recept podle ID (včetně autora, kategorie, obtížnosti,
	 * ingrediencí a kroků postupu).
	 */
	public function getById(int $id): ?SubmissionDTO {
		$stmt = $this->db->prepare('SELECT * FROM submissions WHERE id = :id');
		$stmt->execute(['id' => $id]);

		$row = $stmt->fetch();

		if (!$row) {
			return NULL;
		}

		$base = SubmissionDTO::fromRow($row);

		$authorRepo = new AuthorRepository();
		$categoryRepo = new CategoryRepository();
		$difficultyRepo = new DifficultyRepository();

		return new SubmissionDTO(
			id: $base->id,
			authorId: $base->authorId,
			categoryId: $base->categoryId,
			difficultyId: $base->difficultyId,
			name: $base->name,
			description: $base->description,
			prepTimeMinutes: $base->prepTimeMinutes,
			cookTimeMinutes: $base->cookTimeMinutes,
			servings: $base->servings,
			status: $base->status,
			createdAt: $base->createdAt,
			author: $authorRepo->getById($base->authorId),
			category: $categoryRepo->getById($base->categoryId),
			difficulty: $difficultyRepo->getById($base->difficultyId),
			ingredients: $this->getIngredients($base->id),
			steps: $this->getSteps($base->id),
		);
	}

	/**
	 * Vytvoří nový uživatelem zaslaný recept (submission) včetně všech ingrediencí a kroků.
	 *
	 * Vše proběhne v jedné transakci – pokud selže jakákoliv část, nic se neuloží.
	 *
	 * @param list<array{name: string, amount: ?float, unit_id: ?int, note: string}> $ingredients
	 * @param list<string>                                                            $steps
	 */
	public function create(
		int $authorId,
		int $categoryId,
		int $difficultyId,
		string $name,
		string $description,
		int $prepTimeMinutes,
		int $cookTimeMinutes,
		int $servings,
		array $ingredients,
		array $steps,
	): SubmissionDTO {
		$this->db->beginTransaction();

		try {
			$stmt = $this->db->prepare("
				INSERT INTO submissions (
					author_id, category_id, difficulty_id, name, description,
					prep_time_minutes, cook_time_minutes, servings, status
				)
				VALUES (
					:authorId, :categoryId, :difficultyId, :name, :description,
					:prepTime, :cookTime, :servings, 'pending'
				)
			");

			$stmt->execute([
				'authorId'     => $authorId,
				'categoryId'   => $categoryId,
				'difficultyId' => $difficultyId,
				'name'         => $name,
				'description'  => $description,
				'prepTime'     => $prepTimeMinutes,
				'cookTime'     => $cookTimeMinutes,
				'servings'     => $servings,
			]);

			$submissionId = (int) $this->db->lastInsertId();

			$ingStmt = $this->db->prepare('
				INSERT INTO submission_ingredients (submission_id, name, amount, unit_id, note, sort_order)
				VALUES (:submissionId, :name, :amount, :unitId, :note, :sortOrder)
			');

			foreach ($ingredients as $index => $ing) {
				$ingStmt->execute([
					'submissionId' => $submissionId,
					'name'         => $ing['name'],
					'amount'       => $ing['amount'],
					'unitId'       => $ing['unit_id'],
					'note'         => $ing['note'],
					'sortOrder'    => $index + 1,
				]);
			}

			$stepStmt = $this->db->prepare('
				INSERT INTO submission_steps (submission_id, step_number, description)
				VALUES (:submissionId, :stepNumber, :description)
			');

			foreach ($steps as $index => $description) {
				$stepStmt->execute([
					'submissionId' => $submissionId,
					'stepNumber'   => $index + 1,
					'description'  => $description,
				]);
			}

			$this->db->commit();
		} catch (\Throwable $e) {
			$this->db->rollBack();
			throw $e;
		}

		return $this->getById($submissionId)
			?? throw new \RuntimeException('Nepodařilo se uložit recept.');
	}

	/**
	 * Vrátí ingredience zaslaného receptu.
	 *
	 * @return list<SubmissionIngredientDTO>
	 */
	private function getIngredients(int $submissionId): array {
		$stmt = $this->db->prepare('
			SELECT * FROM submission_ingredients
			WHERE submission_id = :submissionId
			ORDER BY sort_order, id
		');
		$stmt->execute(['submissionId' => $submissionId]);

		return array_map(SubmissionIngredientDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Vrátí kroky postupu zaslaného receptu.
	 *
	 * @return list<SubmissionStepDTO>
	 */
	private function getSteps(int $submissionId): array {
		$stmt = $this->db->prepare('
			SELECT * FROM submission_steps
			WHERE submission_id = :submissionId
			ORDER BY step_number
		');
		$stmt->execute(['submissionId' => $submissionId]);

		return array_map(SubmissionStepDTO::fromRow(...), $stmt->fetchAll());
	}

}
