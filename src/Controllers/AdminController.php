<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\UserService;
use InvalidArgumentException;
use Throwable;

class AdminController
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

  public function list(Request $request): Response
  {
    try {
      $this->authService->getAdmin($request);

      return (new Response())
        ->setData([
          'users' => $this->userService->getUsers(),
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(403)
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
      $this->authService->getAdmin($request);

      $user = $this->userService->getUser(
        (int) ($parameters['id'] ?? 0)
      );

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
        ->setStatusCode(403)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }

  public function update(
    Request $request,
    array $parameters
  ): Response {
    try {
      $this->authService->getAdmin($request);

      $user = $this->userService->adminUpdateUser(
        (int) ($parameters['id'] ?? 0),
        $request->getData()
      );

      return (new Response())
        ->setData([
          'message' => 'User updated successfully',
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
        ->setStatusCode(403)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }

  public function delete(
    Request $request,
    array $parameters
  ): Response {
    try {
      $this->authService->getAdmin($request);

      $this->userService->deleteUser(
        (int) ($parameters['id'] ?? 0)
      );

      return (new Response())
        ->setData([
          'message' => 'User deleted successfully',
        ]);
    } catch (InvalidArgumentException $exception) {
      return (new Response())
        ->setStatusCode(404)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(403)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }
}