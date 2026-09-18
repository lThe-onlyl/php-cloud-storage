<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

class TokenRepository extends Db
{
  public function create(int $userId, string $token): void
  {
    $statement = $this->pdo->prepare(
      'INSERT INTO tokens (user_id, token)
             VALUES (:user_id, :token)'
    );

    $statement->execute([
      'user_id' => $userId,
      'token' => $token,
    ]);
  }

  public function findByToken(string $token): ?array
  {
    return $this->findOneBy('tokens', 'token', $token);
  }

  public function delete(string $token): void
  {
    $statement = $this->pdo->prepare(
      'DELETE FROM tokens WHERE token = :token'
    );

    $statement->execute([
      'token' => $token,
    ]);
  }
}