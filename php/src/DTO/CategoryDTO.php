<?php

declare(strict_types=1);

readonly class CategoryDTO {

	public function __construct(
		public int $id,
		public string $name,
		public string $slug,
		public string $image,
		public string $description,
	) {
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public static function fromRow(array $row): self {
		return new self(
			id: (int) $row['id'],
			name: $row['name'],
			slug: $row['slug'],
			image: $row['image'],
			description: $row['description'],
		);
	}

}
