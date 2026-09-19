<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Controllers\UserController;
use App\Core\App;
use App\Controllers\AdminController;
use App\Controllers\FileController;

$request = new Request();
$router = new Router();
$app = new App();
$adminController = new AdminController($app);
$fileController = new FileController($app);

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

$router->add(
  'POST',
  '/directories/add',
  [$fileController, 'addDirectory']
);

$router->add(
  'GET',
  '/directories/get/{id}',
  [$fileController, 'getDirectory']
);

$router->add(
  'PUT',
  '/directories/rename',
  [$fileController, 'renameDirectory']
);

$router->add(
  'DELETE',
  '/directories/delete/{id}',
  [$fileController, 'deleteDirectory']
);

$router->add(
  'POST',
  '/files/add',
  [$fileController, 'addFile']
);

$router->add(
  'GET',
  '/files/list',
  [$fileController, 'listFiles']
);

$router->add(
  'GET',
  '/files/get/{id}',
  [$fileController, 'getFile']
);

$response = $router->processRequest($request);

$response->send();