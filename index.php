<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Controllers\UserController;
use App\Core\App;
use App\Controllers\AdminController;

$request = new Request();
$router = new Router();
$app = new App();
$adminController = new AdminController($app);

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

$userController = new UserController($app);

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

$router->add(
  'GET',
  '/users/list',
  [$userController, 'list']
);

$router->add(
  'GET',
  '/users/get/{id}',
  [$userController, 'get']
);

$router->add(
  'PUT',
  '/users/update',
  [$userController, 'update']
);

$router->add(
  'GET',
  '/admin/users/list',
  [$adminController, 'list']
);

$router->add(
  'GET',
  '/admin/users/get/{id}',
  [$adminController, 'get']
);

$router->add(
  'PUT',
  '/admin/users/update/{id}',
  [$adminController, 'update']
);

$router->add(
  'DELETE',
  '/admin/users/delete/{id}',
  [$adminController, 'delete']
);

$response = $router->processRequest($request);

$response->send();