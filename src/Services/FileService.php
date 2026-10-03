<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DirectoryRepository;
use App\Repositories\FileRepository;
use InvalidArgumentException;
use RuntimeException;
use App\Repositories\ShareRepository;

class FileService
{
  private FileRepository $fileRepository;
  private DirectoryRepository $directoryRepository;
  private ShareRepository $shareRepository;
  private string $storagePath;

  public function __construct()
  {
    $this->fileRepository = new FileRepository();
    $this->directoryRepository = new DirectoryRepository();
    $this->shareRepository = new ShareRepository();
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
        $this->getUploadErrorMessage(
          (int) $file['error']
        )
      );
    }

    $maxFileSize = 2 * 1024 * 1024 * 1024;

    if ((int) $file['size'] > $maxFileSize) {
      throw new InvalidArgumentException(
        'File size must not exceed 2 GB'
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

    if ($file === null) {
      throw new RuntimeException(
        'File not found'
      );
    }

    $isOwner = (int) $file['user_id'] === $userId;

    $hasAccess = $this->shareRepository->hasAccess(
      $id,
      $userId
    );

    if (!$isOwner && !$hasAccess) {
      throw new RuntimeException(
        'File not found'
      );
    }

    return $file;
  }

  public function getList(int $userId): array
  {
    return $this->fileRepository->getAccessibleByUser($userId);
  }

  public function rename(
    int $userId,
    array $data
  ): array {
    $id = (int) ($data['id'] ?? 0);
    $name = trim($data['name'] ?? '');

    if ($id <= 0 || $name === '') {
      throw new InvalidArgumentException(
        'File id and name are required'
      );
    }

    $this->getOwnedFile($userId, $id);

    $this->fileRepository->rename($id, $name);

    return $this->get($userId, $id);
  }

  public function delete(
    int $userId,
    int $id
  ): void {
    $file = $this->getOwnedFile($userId, $id);

    $path = $this->storagePath . $file['stored_name'];

    if (is_file($path) && !unlink($path)) {
      throw new RuntimeException(
        'Unable to delete file from storage'
      );
    }

    $this->fileRepository->delete($id);
  }

  public function move(
    int $userId,
    array $data
  ): array {
    $id = (int) ($data['id'] ?? 0);

    $directoryId = isset($data['directory_id'])
      && $data['directory_id'] !== ''
      ? (int) $data['directory_id']
      : null;

    $file = $this->getOwnedFile($userId, $id);

    if ($directoryId !== null) {
      $directory = $this->directoryRepository->findById(
        $directoryId
      );

      if (
        $directory === null ||
        (int) $directory['user_id'] !== $userId
      ) {
        throw new RuntimeException(
          'Directory not found'
        );
      }
    }

    $this->fileRepository->move(
      $id,
      $directoryId
    );

    return $this->get($userId, $id);
  }

  private function getUploadErrorMessage(int $error): string
  {
    switch ($error) {
      case UPLOAD_ERR_INI_SIZE:
        return 'File exceeds the server upload limit';

      case UPLOAD_ERR_FORM_SIZE:
        return 'File exceeds the form upload limit';

      case UPLOAD_ERR_PARTIAL:
        return 'File was only partially uploaded';

      case UPLOAD_ERR_NO_FILE:
        return 'File is required';

      case UPLOAD_ERR_NO_TMP_DIR:
        return 'Temporary directory is missing';

      case UPLOAD_ERR_CANT_WRITE:
        return 'Unable to write uploaded file';

      case UPLOAD_ERR_EXTENSION:
        return 'File upload was stopped by an extension';

      default:
        return 'File upload failed';
    }
  }

  private function getOwnedFile(
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
}