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
    $this->routes[$method][] = [
      'route' => $route,
      'handler' => $handler,
    ];
  }

  public function processRequest(Request $request): Response
  {
    $method = $request->getMethod();
    $requestRoute = $request->getRoute();

    foreach ($this->routes[$method] ?? [] as $routeData) {
      $pattern = preg_replace(
        '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
        '(?P<$1>[^/]+)',
        $routeData['route']
      );

      $pattern = '#^' . $pattern . '$#';

      if (preg_match($pattern, $requestRoute, $matches)) {
        $parameters = array_filter(
          $matches,
          'is_string',
          ARRAY_FILTER_USE_KEY
        );

        return call_user_func(
          $routeData['handler'],
          $request,
          $parameters
        );
      }
    }

    return (new Response())
      ->setStatusCode(404)
      ->setData([
        'error' => 'Route not found',
      ]);
  }
}