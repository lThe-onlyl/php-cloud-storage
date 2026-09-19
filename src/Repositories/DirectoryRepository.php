<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

class DirectoryRepository extends Db
{
  public function create(
    int $userId,
    string $name,
    ?int $parentId
  ): int {
    $statement = $this->pdo->prepare(
      'INSERT INTO directories (user_id, name, parent_id)
             VALUES (:user_id, :name, :parent_id)'
    );

    $statement->execute([
      'user_id' => $userId,
      'name' => $name,
      'parent_id' => $parentId,
    ]);

    return (int) $this->pdo->lastInsertId();
  }

  public function findById(int $id): ?array
  {
    return $this->find('directories', $id);
  }

  public function getByParent(
    int $userId,
    ?int $parentId
  ): array {
    if ($parentId === null) {
      $statement = $this->pdo->prepare(
        'SELECT * FROM directories
                 WHERE user_id = :user_id
                 AND parent_id IS NULL'
      );

      $statement->execute([
        'user_id' => $userId,
      ]);

      return $statement->fetchAll();
    }

    $statement = $this->pdo->prepare(
      'SELECT * FROM directories
             WHERE user_id = :user_id
             AND parent_id = :parent_id'
    );

    $statement->execute([
      'user_id' => $userId,
      'parent_id' => $parentId,
    ]);

    return $statement->fetchAll();
  }

  public function rename(
    int $id,
    string $name
  ): void {
    $statement = $this->pdo->prepare(
      'UPDATE directories
             SET name = :name
             WHERE id = :id'
    );

    $statement->execute([
      'id' => $id,
      'name' => $name,
    ]);
  }

  public function delete(int $id): void
  {
    $statement = $this->pdo->prepare(
      'DELETE FROM directories WHERE id = :id'
    );

    $statement->execute([
      'id' => $id,
    ]);
  }
}