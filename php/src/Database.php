<?php

declare(strict_types=1);

final class Database {

	private static ?PDO $connection = NULL;

	private function __construct() {
	}

	public static function getConnection(): PDO {
		if (self::$connection === NULL) {
			self::$connection = new PDO(
				dsn: 'sqlite:' . __DIR__ . '/../database/kucharka.db',
				options: [
					PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
					PDO::ATTR_EMULATE_PREPARES   => FALSE,
				],
			);

			self::$connection->exec('PRAGMA journal_mode = WAL');
			self::$connection->exec('PRAGMA foreign_keys = ON');
		}

		return self::$connection;
	}

}
