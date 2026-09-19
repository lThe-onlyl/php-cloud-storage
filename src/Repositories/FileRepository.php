<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

class FileRepository extends Db
{
  public function create(
    int $userId,
    ?int $directoryId,
    string $originalName,
    string $storedName,
    string $mimeType,
    int $size
  ): int {
    $statement = $this->pdo->prepare(
      'INSERT INTO files (
                user_id,
                directory_id,
                original_name,
                stored_name,
                mime_type,
                size
            ) VALUES (
                :user_id,
                :directory_id,
                :original_name,
                :stored_name,
                :mime_type,
                :size
            )'
    );

    $statement->execute([
      'user_id' => $userId,
      'directory_id' => $directoryId,
      'original_name' => $originalName,
      'stored_name' => $storedName,
      'mime_type' => $mimeType,
      'size' => $size,
    ]);

    return (int) $this->pdo->lastInsertId();
  }

  public function findById(int $id): ?array
  {
    return $this->find('files', $id);
  }

  public function getByUser(int $userId): array
  {
    return $this->findBy('files', 'user_id', $userId);
  }

  public function getByDirectory(
    int $userId,
    int $directoryId
  ): array {
    $statement = $this->pdo->prepare(
      'SELECT * FROM files
             WHERE user_id = :user_id
             AND directory_id = :directory_id'
    );

    $statement->execute([
      'user_id' => $userId,
      'directory_id' => $directoryId,
    ]);

    return $statement->fetchAll();
  }

  public function rename(
    int $id,
    string $name
  ): void {
    $statement = $this->pdo->prepare(
      'UPDATE files
             SET original_name = :name
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
      'DELETE FROM files WHERE id = :id'
    );

    $statement->execute([
      'id' => $id,
    ]);
  }
}