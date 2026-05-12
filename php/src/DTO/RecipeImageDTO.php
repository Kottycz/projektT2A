<?php

declare(strict_types=1);

readonly class RecipeImageDTO {

	public function __construct(
		public int $id,
		public int $recipeId,
		public string $image,
		public int $sortOrder,
	) {
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public static function fromRow(array $row): self {
		return new self(
			id: (int) $row['id'],
			recipeId: (int) $row['recipe_id'],
			image: $row['image'],
			sortOrder: (int) $row['sort_order'],
		);
	}

}
