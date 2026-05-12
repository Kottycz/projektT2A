<?php

declare(strict_types=1);

readonly class SubmissionIngredientDTO {

	public function __construct(
		public int $id,
		public int $submissionId,
		public string $name,
		public ?float $amount,
		public ?int $unitId,
		public string $note,
		public int $sortOrder,
	) {
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public static function fromRow(array $row): self {
		return new self(
			id: (int) $row['id'],
			submissionId: (int) $row['submission_id'],
			name: $row['name'],
			amount: isset($row['amount']) ? (float) $row['amount'] : NULL,
			unitId: isset($row['unit_id']) ? (int) $row['unit_id'] : NULL,
			note: $row['note'] ?? '',
			sortOrder: (int) $row['sort_order'],
		);
	}

}
