<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Controllers\UserController;

$request = new Request();
$router = new Router();

$router->add(
  'GET',
  '/api/test',
  function (Request $request): Response {
    return (new Response())
      ->setData([
        'message' => 'Cloud Storage API is working',
        'method' => $request->getMethod(),
      ]);
  }
);

$userController = new UserController();

$router->add(
  'POST',
  '/users/register',
  [$userController, 'register']
);

$router->add(
  'POST',
  '/users/login',
  [$userController, 'login']
);

$router->add(
  'GET',
  '/users/me',
  [$userController, 'me']
);

$router->add(
  'GET',
  '/users/logout',
  [$userController, 'logout']
);

$response = $router->processRequest($request);

$response->send();