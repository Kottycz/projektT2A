<?php

declare(strict_types=1);

/**
 * Validátor formulářových dat.
 *
 * ÚKOL: Implementujte všechny metody této třídy.
 *
 * Každá validační metoda musí:
 *   1. Zkontrolovat hodnotu podle svého pravidla.
 *   2. Pokud je nevalidní, přidat chybu do pole $errors (klíč = název pole, hodnota = hláška).
 *   3. Vrátit $this (pro řetězení metod – fluent interface).
 *
 * Pro jedno pole se uchovává jen první chyba (aby se nezobrazovalo více hlášek najednou).
 *
 * Příklad použití:
 *   $v = new Validator();
 *   $v->required('email', $email, 'E-mail je povinný.')
 *     ->email('email', $email, 'Neplatný formát e-mailu.')
 *     ->required('name', $name, 'Jméno je povinné.')
 *     ->minLength('name', $name, 2, 'Jméno musí mít alespoň 2 znaky.');
 *
 *   if (!$v->isValid()) {
 *       $errors = $v->getErrors(); // ['email' => 'E-mail je povinný.']
 *   }
 */
final class Validator {

	/** @var array<string, string> pole chyb (klíč = název pole, hodnota = chybová hláška) */
	private array $errors = [];

	/**
	 * Pole nesmí být prázdné (po oříznutí mezer).
	 */
	public function required(string $field, string $value, string $message): self {
		// TODO: implementujte
		return $this;
	}

	/**
	 * Hodnota musí být platný e-mail.
	 * Validujte pouze pokud hodnota není prázdná (prázdnou hodnotu řeší required).
	 *
	 * Tip: použijte filter_var() s FILTER_VALIDATE_EMAIL.
	 */
	public function email(string $field, string $value, string $message): self {
		// TODO: implementujte
		return $this;
	}

	/**
	 * Hodnota musí mít minimální délku.
	 * Validujte pouze pokud hodnota není prázdná.
	 *
	 * Tato metoda slouží jako VZOR pro implementaci ostatních metod.
	 */
	public function minLength(string $field, string $value, int $min, string $message): self {
		if ($value !== '' && mb_strlen($value) < $min) {
			// Uchovává jen první chybu na pole (aby se nezobrazovalo více hlášek najednou)
			$this->errors[$field] ??= $message;
		}

		return $this;
	}

	/**
	 * Hodnota nesmí překročit maximální délku.
	 */
	public function maxLength(string $field, string $value, int $max, string $message): self {
		// TODO: implementujte
		return $this;
	}

	/**
	 * Hodnota musí být celé číslo v rozmezí <min, max>.
	 * Validujte pouze pokud hodnota není prázdná.
	 *
	 * Tip: použijte filter_var() s FILTER_VALIDATE_INT a options ['min_range', 'max_range'].
	 */
	public function intRange(string $field, string $value, int $min, int $max, string $message): self {
		// TODO: implementujte
		return $this;
	}

	/**
	 * Hodnota musí odpovídat regulárnímu výrazu.
	 * Validujte pouze pokud hodnota není prázdná.
	 *
	 * Tip: použijte preg_match().
	 */
	public function pattern(string $field, string $value, string $regex, string $message): self {
		// TODO: implementujte
		return $this;
	}

	/**
	 * Hodnota musí být jedno z povolených hodnot.
	 *
	 * Tip: použijte in_array() se strict porovnáním.
	 *
	 * @param list<string|int> $allowed
	 */
	public function in(string $field, string|int $value, array $allowed, string $message): self {
		// TODO: implementujte
		return $this;
	}

	/**
	 * Jsou data validní (žádné chyby)?
	 */
	public function isValid(): bool {
		// TODO: implementujte
		return true;
	}

	/**
	 * Vrátí všechny chyby.
	 *
	 * @return array<string, string>
	 */
	public function getErrors(): array {
		// TODO: implementujte
		return [];
	}

	/**
	 * Vrátí chybu pro konkrétní pole (nebo null, pokud pole nemá chybu).
	 */
	public function getError(string $field): ?string {
		// TODO: implementujte
		return null;
	}

	/**
	 * Má dané pole chybu?
	 */
	public function hasError(string $field): bool {
		// TODO: implementujte
		return false;
	}

}
