<?php

declare(strict_types=1);

readonly class RecipeStepDTO {

	public function __construct(
		public int $id,
		public int $recipeId,
		public int $stepNumber,
		public string $description,
	) {
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public static function fromRow(array $row): self {
		return new self(
			id: (int) $row['id'],
			recipeId: (int) $row['recipe_id'],
			stepNumber: (int) $row['step_number'],
			description: $row['description'],
		);
	}

}
