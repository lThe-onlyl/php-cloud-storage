<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\UserService;
use InvalidArgumentException;
use Throwable;
use App\Services\AuthService;

class UserController
{
  private UserService $userService;
  private AuthService $authService;

  public function __construct()
  {
    $this->userService = new UserService();
    $this->authService = new AuthService();
  }

  public function register(Request $request): Response
  {
    try {
      $user = $this->userService->register(
        $request->getData()
      );

      return (new Response())
        ->setStatusCode(201)
        ->setData([
          'message' => 'User registered successfully',
          'user' => $user,
        ]);
    } catch (InvalidArgumentException $exception) {
      return (new Response())
        ->setStatusCode(400)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(500)
        ->setData([
          'error' => 'Internal server error',
        ]);
    }
  }

  public function login(Request $request): Response
  {
    try {
      $result = $this->userService->login(
        $request->getData()
      );

      return (new Response())
        ->setData([
          'message' => 'Login successful',
          'token' => $result['token'],
          'user' => $result['user'],
        ]);
    } catch (InvalidArgumentException $exception) {
      return (new Response())
        ->setStatusCode(401)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(500)
        ->setData([
          'error' => 'Internal server error',
        ]);
    }
  }

  public function me(Request $request): Response
  {
    try {
      $user = $this->authService->getUser($request);

      return (new Response())
        ->setData([
          'user' => [
            'id' => (int) $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'role' => $user['role'],
          ],
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(401)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }

  public function logout(Request $request): Response
  {
    try {
      $this->authService->logout($request);

      return (new Response())
        ->setData([
          'message' => 'Logout successful',
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(401)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }
}
