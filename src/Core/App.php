<?php

declare(strict_types=1);

namespace App\Core;

use App\Services\AuthService;
use App\Services\UserService;
use InvalidArgumentException;
use App\Services\DirectoryService;
use App\Services\FileService;
use App\Services\ShareService;

class App
{
  private array $services = [];

  public function __construct()
  {
    $this->services = [
      'user' => new UserService(),
      'auth' => new AuthService(),
      'directory' => new DirectoryService(),
      'file' => new FileService(),
      'share' => new ShareService(),
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