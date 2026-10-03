<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PasswordResetRepository;
use App\Repositories\UserRepository;
use InvalidArgumentException;
use RuntimeException;

class PasswordResetService
{
  private PasswordResetRepository $resetRepository;
  private UserRepository $userRepository;

  public function __construct()
  {
    $this->resetRepository = new PasswordResetRepository();
    $this->userRepository = new UserRepository();
  }

  public function createResetToken(string $email): array
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

    $this->resetRepository->deleteByUser(
      (int) $user['id']
    );

    $token = bin2hex(random_bytes(32));

    $expiresAt = date(
      'Y-m-d H:i:s',
      time() + 3600
    );

    $this->resetRepository->create(
      (int) $user['id'],
      $token,
      $expiresAt
    );

    return [
      'token' => $token,
      'expires_at' => $expiresAt,
    ];
  }

  public function resetPassword(
    string $token,
    string $password
  ): void {
    $token = trim($token);

    if ($token === '') {
      throw new InvalidArgumentException(
        'Reset token is required'
      );
    }

    if (strlen($password) < 6) {
      throw new InvalidArgumentException(
        'Password must contain at least 6 characters'
      );
    }

    $reset = $this->resetRepository->findByToken(
      $token
    );

    if ($reset === null) {
      throw new RuntimeException(
        'Invalid reset token'
      );
    }

    if (strtotime($reset['expires_at']) < time()) {
      $this->resetRepository->deleteByToken($token);

      throw new RuntimeException(
        'Reset token has expired'
      );
    }

    $hashedPassword = password_hash(
      $password,
      PASSWORD_DEFAULT
    );

    $this->userRepository->updatePassword(
      (int) $reset['user_id'],
      $hashedPassword
    );

    $this->resetRepository->deleteByToken($token);
  }
}