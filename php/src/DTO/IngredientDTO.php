<?php

declare(strict_types=1);

readonly class IngredientDTO {

	public function __construct(
		public int $id,
		public int $recipeId,
		public string $name,
		public ?float $amount,
		public ?int $unitId,
		public string $note,
		public int $sortOrder,
		public ?string $unitName = NULL,
		public ?string $unitAbbreviation = NULL,
	) {
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public static function fromRow(array $row): self {
		return new self(
			id: (int) $row['id'],
			recipeId: (int) $row['recipe_id'],
			name: $row['name'],
			amount: isset($row['amount']) ? (float) $row['amount'] : NULL,
			unitId: isset($row['unit_id']) ? (int) $row['unit_id'] : NULL,
			note: $row['note'] ?? '',
			sortOrder: (int) $row['sort_order'],
			unitName: $row['unit_name'] ?? NULL,
			unitAbbreviation: $row['unit_abbreviation'] ?? NULL,
		);
	}

	/**
	 * Vrátí formátované množství + jednotku (např. "300 g" nebo "1.5 l").
	 * Pokud nemá množství (např. "sůl podle chuti"), vrátí prázdný řetězec.
	 */
	public function getFormattedAmount(): string {
		if ($this->amount === NULL) {
			return '';
		}

		// Pro celá čísla zobrazíme bez desetinných míst
		$formatted = floor($this->amount) === $this->amount
			? (string) (int) $this->amount
			: rtrim(rtrim(number_format($this->amount, 2, ',', ''), '0'), ',');

		return $this->unitAbbreviation !== NULL && $this->unitAbbreviation !== ''
			? $formatted . ' ' . $this->unitAbbreviation
			: $formatted;
	}

}
