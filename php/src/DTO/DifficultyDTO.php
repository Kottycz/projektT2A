<?php

declare(strict_types=1);

readonly class DifficultyDTO {

	public function __construct(
		public int $id,
		public string $name,
		public int $sortOrder,
	) {
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public static function fromRow(array $row): self {
		return new self(
			id: (int) $row['id'],
			name: $row['name'],
			sortOrder: (int) $row['sort_order'],
		);
	}

}
