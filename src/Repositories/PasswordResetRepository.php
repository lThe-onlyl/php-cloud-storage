<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

class PasswordResetRepository extends Db
{
  public function create(
    int $userId,
    string $token,
    string $expiresAt
  ): void {
    $statement = $this->pdo->prepare(
      'INSERT INTO password_resets (
                user_id,
                token,
                expires_at
            ) VALUES (
                :user_id,
                :token,
                :expires_at
            )'
    );

    $statement->execute([
      'user_id' => $userId,
      'token' => $token,
      'expires_at' => $expiresAt,
    ]);
  }

  public function findByToken(string $token): ?array
  {
    $statement = $this->pdo->prepare(
      'SELECT *
             FROM password_resets
             WHERE token = :token
             LIMIT 1'
    );

    $statement->execute([
      'token' => $token,
    ]);

    $result = $statement->fetch();

    return $result ?: null;
  }

  public function deleteByToken(string $token): void
  {
    $statement = $this->pdo->prepare(
      'DELETE FROM password_resets
             WHERE token = :token'
    );

    $statement->execute([
      'token' => $token,
    ]);
  }

  public function deleteByUser(int $userId): void
  {
    $statement = $this->pdo->prepare(
      'DELETE FROM password_resets
             WHERE user_id = :user_id'
    );

    $statement->execute([
      'user_id' => $userId,
    ]);
  }
}