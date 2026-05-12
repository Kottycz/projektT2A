<?php

declare(strict_types=1);

/**
 * Oblíbené recepty – jednoduchý seznam ID receptů uložených v session.
 *
 * Obdoba košíku z e-shopu, ale výrazně jednodušší: nesleduje množství ani varianty,
 * jen "uživatel označil tento recept jako oblíbený".
 */
final class Favorites {

	private const string SESSION_KEY = 'favorites';

	public function __construct() {
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
	}

	/**
	 * Přidá recept mezi oblíbené (pokud tam ještě není).
	 */
	public function add(int $recipeId): void {
		$ids = $this->getIds();

		if (!in_array($recipeId, $ids, true)) {
			$ids[] = $recipeId;
		}

		$_SESSION[self::SESSION_KEY] = $ids;
	}

	/**
	 * Odebere recept z oblíbených.
	 */
	public function remove(int $recipeId): void {
		$ids = array_values(array_filter(
			$this->getIds(),
			fn(int $id): bool => $id !== $recipeId,
		));

		$_SESSION[self::SESSION_KEY] = $ids;
	}

	/**
	 * Přepne stav (přidá pokud chybí, odebere pokud je tam).
	 */
	public function toggle(int $recipeId): void {
		if ($this->contains($recipeId)) {
			$this->remove($recipeId);
		} else {
			$this->add($recipeId);
		}
	}

	/**
	 * Je daný recept mezi oblíbenými?
	 */
	public function contains(int $recipeId): bool {
		return in_array($recipeId, $this->getIds(), true);
	}

	/**
	 * Vrátí pole ID všech oblíbených receptů.
	 *
	 * @return list<int>
	 */
	public function getIds(): array {
		$ids = $_SESSION[self::SESSION_KEY] ?? [];

		// Defensivně očistíme od neceločíselných hodnot (pro případ poškozené session)
		return array_values(array_map(intval(...), array_filter($ids, is_numeric(...))));
	}

	/**
	 * Vrátí počet oblíbených receptů.
	 */
	public function count(): int {
		return count($this->getIds());
	}

	/**
	 * Je seznam oblíbených prázdný?
	 */
	public function isEmpty(): bool {
		return $this->getIds() === [];
	}

	/**
	 * Vyprázdní seznam oblíbených.
	 */
	public function clear(): void {
		$_SESSION[self::SESSION_KEY] = [];
	}

}
