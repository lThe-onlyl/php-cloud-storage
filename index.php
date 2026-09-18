<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

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

$response = $router->processRequest($request);

$response->send();