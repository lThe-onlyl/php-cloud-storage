<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Request;
use App\Repositories\TokenRepository;
use App\Repositories\UserRepository;
use RuntimeException;

class AuthService
{
  private TokenRepository $tokenRepository;
  private UserRepository $userRepository;

  public function __construct()
  {
    $this->tokenRepository = new TokenRepository();
    $this->userRepository = new UserRepository();
  }

  public function getToken(Request $request): string
  {
    $authorization = $request->getHeader('Authorization');

    if ($authorization === null) {
      throw new RuntimeException(
        'Authorization token is required'
      );
    }

    if (!preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
      throw new RuntimeException(
        'Invalid authorization header'
      );
    }

    return trim($matches[1]);
  }

  public function getUser(Request $request): array
  {
    $token = $this->getToken($request);

    $tokenData = $this->tokenRepository->findByToken($token);

    if ($tokenData === null) {
      throw new RuntimeException(
        'Invalid authorization token'
      );
    }

    $user = $this->userRepository->findById(
      (int) $tokenData['user_id']
    );

    if ($user === null) {
      throw new RuntimeException(
        'User not found'
      );
    }

    return $user;
  }

  public function logout(Request $request): void
  {
    $token = $this->getToken($request);

    if ($this->tokenRepository->findByToken($token) === null) {
      throw new RuntimeException(
        'Invalid authorization token'
      );
    }

    $this->tokenRepository->delete($token);
  }

  public function getAdmin(Request $request): array
  {
    $user = $this->getUser($request);

    if ($user['role'] !== 'admin') {
      throw new RuntimeException(
        'Administrator access required'
      );
    }

    return $user;
  }
}