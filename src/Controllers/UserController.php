<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\UserService;
use InvalidArgumentException;
use Throwable;
use App\Services\AuthService;
use App\Core\App;

class UserController
{
  private UserService $userService;
  private AuthService $authService;

  public function __construct(App $app)
  {
    /** @var UserService $userService */
    $userService = $app->getService('user');

    /** @var AuthService $authService */
    $authService = $app->getService('auth');

    $this->userService = $userService;
    $this->authService = $authService;
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

  public function list(Request $request): Response
  {
    try {
      $this->authService->getUser($request);

      return (new Response())
        ->setData([
          'users' => $this->userService->getUsers(),
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(401)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }

  public function get(
    Request $request,
    array $parameters
  ): Response {
    try {
      $this->authService->getUser($request);

      $id = (int) ($parameters['id'] ?? 0);

      $user = $this->userService->getUser($id);

      return (new Response())
        ->setData([
          'user' => $user,
        ]);
    } catch (InvalidArgumentException $exception) {
      return (new Response())
        ->setStatusCode(404)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(401)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }

  public function update(Request $request): Response
  {
    try {
      $currentUser = $this->authService->getUser($request);

      $user = $this->userService->updateProfile(
        (int) $currentUser['id'],
        $request->getData()
      );

      return (new Response())
        ->setData([
          'message' => 'Profile updated successfully',
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
        ->setStatusCode(401)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }

  public function search(
    Request $request,
    array $parameters
  ): Response {
    try {
      $this->authService->getUser($request);

      $email = $parameters['email'] ?? '';

      $user = $this->userService->searchByEmail($email);

      return (new Response())
        ->setData([
          'user' => $user,
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(404)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }
}
