<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
  private mixed $data = null;
  private int $statusCode = 200;
  private array $headers = [];

  public function setData(mixed $data): self
  {
    $this->data = $data;

    return $this;
  }

  public function setStatusCode(int $statusCode): self
  {
    $this->statusCode = $statusCode;

    return $this;
  }

  public function setHeaders(array $headers): self
  {
    $this->headers = $headers;

    return $this;
  }

  public function send(): void
  {
    http_response_code($this->statusCode);

    foreach ($this->headers as $name => $value) {
      header("$name: $value");
    }

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(
      $this->data,
      JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );
  }
}