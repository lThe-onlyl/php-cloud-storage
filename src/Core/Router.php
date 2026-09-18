<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
  private array $routes = [];

  public function add(
    string $method,
    string $route,
    callable $handler
  ): void {
    $this->routes[$method][$route] = $handler;
  }

  public function processRequest(Request $request): Response
  {
    $method = $request->getMethod();
    $route = $request->getRoute();

    if (!isset($this->routes[$method][$route])) {
      return (new Response())
        ->setStatusCode(404)
        ->setData([
          'error' => 'Route not found',
        ]);
    }

    $handler = $this->routes[$method][$route];

    return $handler($request);
  }
}