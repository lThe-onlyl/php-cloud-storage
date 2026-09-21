<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

class ShareRepository extends Db
{
  public function create(
    int $fileId,
    int $userId
  ): void {
    $statement = $this->pdo->prepare(
      'INSERT INTO file_shares (file_id, user_id)
             VALUES (:file_id, :user_id)'
    );

    $statement->execute([
      'file_id' => $fileId,
      'user_id' => $userId,
    ]);
  }

  public function findShare(
    int $fileId,
    int $userId
  ): ?array {
    $statement = $this->pdo->prepare(
      'SELECT *
             FROM file_shares
             WHERE file_id = :file_id
             AND user_id = :user_id
             LIMIT 1'
    );

    $statement->execute([
      'file_id' => $fileId,
      'user_id' => $userId,
    ]);

    $result = $statement->fetch();

    return $result ?: null;
  }

  public function getByFile(int $fileId): array
  {
    $statement = $this->pdo->prepare(
      'SELECT
                u.id,
                u.email,
                u.name,
                fs.created_at
             FROM file_shares fs
             INNER JOIN users u
                 ON u.id = fs.user_id
             WHERE fs.file_id = :file_id'
    );

    $statement->execute([
      'file_id' => $fileId,
    ]);

    return $statement->fetchAll();
  }

  public function delete(
    int $fileId,
    int $userId
  ): void {
    $statement = $this->pdo->prepare(
      'DELETE FROM file_shares
             WHERE file_id = :file_id
             AND user_id = :user_id'
    );

    $statement->execute([
      'file_id' => $fileId,
      'user_id' => $userId,
    ]);
  }

  public function hasAccess(
    int $fileId,
    int $userId
  ): bool {
    return $this->findShare($fileId, $userId) !== null;
  }
}