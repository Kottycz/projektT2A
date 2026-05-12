<?php

declare(strict_types=1);

readonly class SubmissionDTO {

	/**
	 * @param list<SubmissionIngredientDTO> $ingredients
	 * @param list<SubmissionStepDTO>       $steps
	 */
	public function __construct(
		public int $id,
		public int $authorId,
		public int $categoryId,
		public int $difficultyId,
		public string $name,
		public string $description,
		public int $prepTimeMinutes,
		public int $cookTimeMinutes,
		public int $servings,
		public string $status,
		public string $createdAt,
		public ?AuthorDTO $author = NULL,
		public ?CategoryDTO $category = NULL,
		public ?DifficultyDTO $difficulty = NULL,
		public array $ingredients = [],
		public array $steps = [],
	) {
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public static function fromRow(array $row): self {
		return new self(
			id: (int) $row['id'],
			authorId: (int) $row['author_id'],
			categoryId: (int) $row['category_id'],
			difficultyId: (int) $row['difficulty_id'],
			name: $row['name'],
			description: $row['description'],
			prepTimeMinutes: (int) $row['prep_time_minutes'],
			cookTimeMinutes: (int) $row['cook_time_minutes'],
			servings: (int) $row['servings'],
			status: $row['status'],
			createdAt: $row['created_at'],
		);
	}

}
