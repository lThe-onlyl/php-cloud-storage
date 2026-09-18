<?php

declare(strict_types=1);

namespace App\Core;

use App\Services\AuthService;
use App\Services\UserService;
use InvalidArgumentException;

class App
{
  private array $services = [];

  public function __construct()
  {
    $this->services = [
      'user' => new UserService(),
      'auth' => new AuthService(),
    ];
  }

  public function getService(string $name): object
  {
    if (!isset($this->services[$name])) {
      throw new InvalidArgumentException(
        "Service '{$name}' is not registered"
      );
    }

    return $this->services[$name];
  }
}