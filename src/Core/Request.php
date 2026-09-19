<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
  private array $data;
  private string $route;
  private string $method;

  public function __construct()
  {
    $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    $uri = parse_url(
      $_SERVER['REQUEST_URI'] ?? '/',
      PHP_URL_PATH
    ) ?? '/';

    $scriptDirectory = dirname($_SERVER['SCRIPT_NAME']);

    if (
      $scriptDirectory !== '/' &&
      str_starts_with($uri, $scriptDirectory)
    ) {
      $uri = substr($uri, strlen($scriptDirectory));
    }

    $this->route = '/' . ltrim($uri, '/');

    $this->data = $this->collectData();
  }

  public function getData(): array
  {
    return $this->data;
  }

  public function getRoute(): string
  {
    return $this->route;
  }

  public function getMethod(): string
  {
    return $this->method;
  }

  private function collectData(): array
  {
    if ($this->method === 'GET') {
      return $_GET;
    }

    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (str_contains($contentType, 'application/json')) {
      $body = file_get_contents('php://input');
      $data = json_decode($body ?: '', true);

      return is_array($data) ? $data : [];
    }

    return $_POST;
  }
  public function getHeader(string $name): ?string
  {
    $key = 'HTTP_' . strtoupper(
      str_replace('-', '_', $name)
    );

    if (isset($_SERVER[$key])) {
      return $_SERVER[$key];
    }

    if (
      strtolower($name) === 'authorization' &&
      isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])
    ) {
      return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }

    return null;
  }

  public function getFiles(): array
  {
    return $_FILES;
  }
}