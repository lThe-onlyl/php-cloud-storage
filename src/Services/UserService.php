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
}