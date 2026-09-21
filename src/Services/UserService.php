<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use InvalidArgumentException;
use App\Repositories\TokenRepository;

class UserService
{
  private UserRepository $userRepository;
  private TokenRepository $tokenRepository;

  public function __construct()
  {
    $this->userRepository = new UserRepository();
    $this->tokenRepository = new TokenRepository();
  }

  public function register(array $data): array
  {
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $name = trim($data['name'] ?? '');

    if ($email === '' || $password === '' || $name === '') {
      throw new InvalidArgumentException(
        'Email, password and name are required'
      );
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new InvalidArgumentException(
        'Invalid email'
      );
    }

    if (strlen($password) < 6) {
      throw new InvalidArgumentException(
        'Password must contain at least 6 characters'
      );
    }

    if ($this->userRepository->findByEmail($email) !== null) {
      throw new InvalidArgumentException(
        'User with this email already exists'
      );
    }

    $passwordHash = password_hash(
      $password,
      PASSWORD_DEFAULT
    );

    $userId = $this->userRepository->create(
      $email,
      $passwordHash,
      $name
    );

    return [
      'id' => $userId,
      'email' => $email,
      'name' => $name,
    ];
  }

  public function login(array $data): array
  {
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if ($email === '' || $password === '') {
      throw new InvalidArgumentException(
        'Email and password are required'
      );
    }

    $user = $this->userRepository->findByEmail($email);

    if (
      $user === null ||
      !password_verify($password, $user['password'])
    ) {
      throw new InvalidArgumentException(
        'Invalid email or password'
      );
    }

    $token = bin2hex(random_bytes(32));

    $this->tokenRepository->create(
      (int) $user['id'],
      $token
    );

    return [
      'token' => $token,
      'user' => [
        'id' => (int) $user['id'],
        'email' => $user['email'],
        'name' => $user['name'],
        'role' => $user['role'],
      ],
    ];
  }

  public function getUsers(): array
  {
    return $this->userRepository->getAll();
  }

  public function getUser(int $id): array
  {
    $user = $this->userRepository->findById($id);

    if ($user === null) {
      throw new InvalidArgumentException(
        'User not found'
      );
    }

    return [
      'id' => (int) $user['id'],
      'email' => $user['email'],
      'name' => $user['name'],
      'role' => $user['role'],
      'created_at' => $user['created_at'],
    ];
  }

  public function updateProfile(
    int $userId,
    array $data
  ): array {
    $user = $this->userRepository->findById($userId);

    if ($user === null) {
      throw new InvalidArgumentException(
        'User not found'
      );
    }

    $email = trim($data['email'] ?? $user['email']);
    $name = trim($data['name'] ?? $user['name']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new InvalidArgumentException(
        'Invalid email'
      );
    }

    if ($name === '') {
      throw new InvalidArgumentException(
        'Name is required'
      );
    }

    $existingUser = $this->userRepository->findByEmail($email);

    if (
      $existingUser !== null &&
      (int) $existingUser['id'] !== $userId
    ) {
      throw new InvalidArgumentException(
        'User with this email already exists'
      );
    }

    $this->userRepository->update(
      $userId,
      $email,
      $name
    );

    return $this->getUser($userId);
  }

  public function deleteUser(int $id): void
  {
    $user = $this->userRepository->findById($id);

    if ($user === null) {
      throw new InvalidArgumentException(
        'User not found'
      );
    }

    $this->userRepository->deleteById($id);
  }

  public function adminUpdateUser(
    int $id,
    array $data
  ): array {
    $user = $this->userRepository->findById($id);

    if ($user === null) {
      throw new InvalidArgumentException(
        'User not found'
      );
    }

    $email = trim($data['email'] ?? $user['email']);
    $name = trim($data['name'] ?? $user['name']);
    $role = $data['role'] ?? $user['role'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new InvalidArgumentException('Invalid email');
    }

    if ($name === '') {
      throw new InvalidArgumentException('Name is required');
    }

    if (!in_array($role, ['user', 'admin'], true)) {
      throw new InvalidArgumentException('Invalid role');
    }

    $existingUser = $this->userRepository->findByEmail($email);

    if (
      $existingUser !== null &&
      (int) $existingUser['id'] !== $id
    ) {
      throw new InvalidArgumentException(
        'User with this email already exists'
      );
    }

    $this->userRepository->adminUpdate(
      $id,
      $email,
      $name,
      $role
    );

    return $this->getUser($id);
  }

  public function searchByEmail(string $email): array
  {
    $email = trim($email);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new InvalidArgumentException(
        'Invalid email'
      );
    }

    $user = $this->userRepository->findByEmail($email);

    if ($user === null) {
      throw new InvalidArgumentException(
        'User not found'
      );
    }

    return [
      'id' => (int) $user['id'],
      'email' => $user['email'],
      'name' => $user['name'],
    ];
  }
}