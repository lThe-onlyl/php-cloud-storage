<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

class UserRepository extends Db
{
  public function findByEmail(string $email): ?array
  {
    return $this->findOneBy('users', 'email', $email);
  }

  public function create(
    string $email,
    string $password,
    string $name
  ): int {
    $statement = $this->pdo->prepare(
      'INSERT INTO users (email, password, name)
             VALUES (:email, :password, :name)'
    );

    $statement->execute([
      'email' => $email,
      'password' => $password,
      'name' => $name,
    ]);

    return (int) $this->pdo->lastInsertId();
  }

  public function findById(int $id): ?array
  {
    return $this->find('users', $id);
  }
}