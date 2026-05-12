<?php

declare(strict_types=1);

readonly class UnitDTO {

	public function __construct(
		public int $id,
		public string $name,
		public string $abbreviation,
	) {
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public static function fromRow(array $row): self {
		return new self(
			id: (int) $row['id'],
			name: $row['name'],
			abbreviation: $row['abbreviation'],
		);
	}

}
