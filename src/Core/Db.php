<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Db
{
  private static ?PDO $connection = null;

  protected PDO $pdo;

  public function __construct()
  {
    $this->pdo = self::getConnection();
  }

  public static function getConnection(): PDO
  {
    if (self::$connection === null) {
      self::$connection = self::createConnection();
    }

    return self::$connection;
  }

  private static function createConnection(): PDO
  {
    $config = require __DIR__ . '/../../config/database.php';

    $dsn = sprintf(
      'mysql:host=%s;dbname=%s;charset=%s',
      $config['host'],
      $config['database'],
      $config['charset']
    );

    try {
      return new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES => false,
        ]
      );
    } catch (PDOException $exception) {
      throw new RuntimeException(
        'Database connection failed',
        0,
        $exception
      );
    }
  }

  protected function findAll(string $table): array
  {
    $statement = $this->pdo->query("SELECT * FROM `$table`");

    return $statement->fetchAll();
  }

  protected function find(string $table, int $id): ?array
  {
    $statement = $this->pdo->prepare(
      "SELECT * FROM `$table` WHERE id = :id"
    );

    $statement->execute([
      'id' => $id,
    ]);

    $result = $statement->fetch();

    return $result ?: null;
  }

  protected function findOneBy(
    string $table,
    string $field,
    mixed $value
  ): ?array {
    $statement = $this->pdo->prepare(
      "SELECT * FROM `$table` WHERE `$field` = :value LIMIT 1"
    );

    $statement->execute([
      'value' => $value,
    ]);

    $result = $statement->fetch();

    return $result ?: null;
  }

  protected function findBy(
    string $table,
    string $field,
    mixed $value
  ): array {
    $statement = $this->pdo->prepare(
      "SELECT * FROM `$table` WHERE `$field` = :value"
    );

    $statement->execute([
      'value' => $value,
    ]);

    return $statement->fetchAll();
  }
}