<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DirectoryRepository;
use App\Repositories\FileRepository;
use InvalidArgumentException;
use RuntimeException;

class FileService
{
  private FileRepository $fileRepository;
  private DirectoryRepository $directoryRepository;

  private string $storagePath;

  public function __construct()
  {
    $this->fileRepository = new FileRepository();
    $this->directoryRepository = new DirectoryRepository();

    $this->storagePath = dirname(__DIR__, 2)
      . '/storage/files/';
  }

  public function upload(
    int $userId,
    array $file,
    ?int $directoryId
  ): array {
    if (
      !isset(
      $file['name'],
      $file['tmp_name'],
      $file['size'],
      $file['error']
    )
    ) {
      throw new InvalidArgumentException(
        'File is required'
      );
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
      throw new RuntimeException(
        'File upload failed'
      );
    }

    if ($directoryId !== null) {
      $directory = $this->directoryRepository
        ->findById($directoryId);

      if (
        $directory === null ||
        (int) $directory['user_id'] !== $userId
      ) {
        throw new RuntimeException(
          'Directory not found'
        );
      }
    }

    if (!is_dir($this->storagePath)) {
      if (
        !mkdir(
          $this->storagePath,
          0775,
          true
        ) &&
        !is_dir($this->storagePath)
      ) {
        throw new RuntimeException(
          'Unable to create storage directory'
        );
      }
    }

    $originalName = basename($file['name']);

    $extension = pathinfo(
      $originalName,
      PATHINFO_EXTENSION
    );

    $storedName = bin2hex(random_bytes(16));

    if ($extension !== '') {
      $storedName .= '.' . strtolower($extension);
    }

    $destination = $this->storagePath . $storedName;

    if (
      !move_uploaded_file(
        $file['tmp_name'],
        $destination
      )
    ) {
      throw new RuntimeException(
        'Unable to save uploaded file'
      );
    }

    $mimeType = mime_content_type($destination);

    if ($mimeType === false) {
      $mimeType = 'application/octet-stream';
    }

    try {
      $fileId = $this->fileRepository->create(
        $userId,
        $directoryId,
        $originalName,
        $storedName,
        $mimeType,
        (int) $file['size']
      );
    } catch (\Throwable $exception) {
      if (is_file($destination)) {
        unlink($destination);
      }

      throw $exception;
    }

    return $this->get($userId, $fileId);
  }

  public function get(
    int $userId,
    int $id
  ): array {
    $file = $this->fileRepository->findById($id);

    if (
      $file === null ||
      (int) $file['user_id'] !== $userId
    ) {
      throw new RuntimeException(
        'File not found'
      );
    }

    return $file;
  }

  public function getList(int $userId): array
  {
    return $this->fileRepository->getByUser($userId);
  }
}