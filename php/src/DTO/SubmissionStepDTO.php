<?php

declare(strict_types=1);

readonly class SubmissionStepDTO {

	public function __construct(
		public int $id,
		public int $submissionId,
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
			submissionId: (int) $row['submission_id'],
			stepNumber: (int) $row['step_number'],
			description: $row['description'],
		);
	}

}
