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

  public function getAll(): array
  {
    $statement = $this->pdo->query(
      'SELECT id, email, name, role, created_at
         FROM users'
    );

    return $statement->fetchAll();
  }

  public function update(
    int $id,
    string $email,
    string $name
  ): void {
    $statement = $this->pdo->prepare(
      'UPDATE users
         SET email = :email, name = :name
         WHERE id = :id'
    );

    $statement->execute([
      'id' => $id,
      'email' => $email,
      'name' => $name,
    ]);
  }

  public function deleteById(int $id): void
  {
    $statement = $this->pdo->prepare(
      'DELETE FROM users WHERE id = :id'
    );

    $statement->execute([
      'id' => $id,
    ]);
  }

  public function adminUpdate(
    int $id,
    string $email,
    string $name,
    string $role
  ): void {
    $statement = $this->pdo->prepare(
      'UPDATE users
         SET email = :email,
             name = :name,
             role = :role
         WHERE id = :id'
    );

    $statement->execute([
      'id' => $id,
      'email' => $email,
      'name' => $name,
      'role' => $role,
    ]);
  }
}