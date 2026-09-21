<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DirectoryRepository;
use InvalidArgumentException;
use RuntimeException;

class DirectoryService
{
  private DirectoryRepository $directoryRepository;

  public function __construct()
  {
    $this->directoryRepository = new DirectoryRepository();
  }

  public function create(
    int $userId,
    array $data
  ): array {
    $name = trim($data['name'] ?? '');

    if ($name === '') {
      throw new InvalidArgumentException(
        'Directory name is required'
      );
    }

    $parentId = isset($data['parent_id'])
      ? (int) $data['parent_id']
      : null;

    if ($parentId !== null) {
      $parent = $this->directoryRepository->findById(
        $parentId
      );

      if (
        $parent === null ||
        (int) $parent['user_id'] !== $userId
      ) {
        throw new RuntimeException(
          'Parent directory not found'
        );
      }
    }

    $id = $this->directoryRepository->create(
      $userId,
      $name,
      $parentId
    );

    return $this->get($userId, $id);
  }

  public function get(
    int $userId,
    int $id
  ): array {
    $directory = $this->directoryRepository->findById($id);

    if (
      $directory === null ||
      (int) $directory['user_id'] !== $userId
    ) {
      throw new RuntimeException(
        'Directory not found'
      );
    }

    return $directory;
  }

  public function rename(
    int $userId,
    array $data
  ): array {
    $id = (int) ($data['id'] ?? 0);
    $name = trim($data['name'] ?? '');

    if ($id <= 0 || $name === '') {
      throw new InvalidArgumentException(
        'Directory id and name are required'
      );
    }

    $this->get($userId, $id);

    $this->directoryRepository->rename($id, $name);

    return $this->get($userId, $id);
  }

  public function delete(
    int $userId,
    int $id
  ): void {
    $this->get($userId, $id);

    $this->directoryRepository->delete($id);
  }

  public function getWithFiles(
    int $userId,
    int $id
  ): array {
    $directory = $this->get($userId, $id);

    return [
      'id' => (int) $directory['id'],
      'user_id' => (int) $directory['user_id'],
      'parent_id' => $directory['parent_id'] !== null
        ? (int) $directory['parent_id']
        : null,
      'name' => $directory['name'],
      'created_at' => $directory['created_at'],
      'files' => $this->directoryRepository->getFiles(
        $userId,
        $id
      ),
    ];
  }
}