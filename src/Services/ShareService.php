<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\FileRepository;
use App\Repositories\ShareRepository;
use App\Repositories\UserRepository;
use InvalidArgumentException;
use RuntimeException;

class ShareService
{
  private ShareRepository $shareRepository;
  private FileRepository $fileRepository;
  private UserRepository $userRepository;

  public function __construct()
  {
    $this->shareRepository = new ShareRepository();
    $this->fileRepository = new FileRepository();
    $this->userRepository = new UserRepository();
  }

  public function getShares(
    int $ownerId,
    int $fileId
  ): array {
    $this->getOwnedFile($ownerId, $fileId);

    return $this->shareRepository->getByFile($fileId);
  }

  public function share(
    int $ownerId,
    int $fileId,
    int $userId
  ): array {
    $this->getOwnedFile($ownerId, $fileId);

    $user = $this->userRepository->findById($userId);

    if ($user === null) {
      throw new InvalidArgumentException(
        'User not found'
      );
    }

    if ($ownerId === $userId) {
      throw new InvalidArgumentException(
        'File is already owned by this user'
      );
    }

    if (
      $this->shareRepository->findShare(
        $fileId,
        $userId
      ) !== null
    ) {
      throw new InvalidArgumentException(
        'File is already shared with this user'
      );
    }

    $this->shareRepository->create(
      $fileId,
      $userId
    );

    return [
      'file_id' => $fileId,
      'user' => [
        'id' => (int) $user['id'],
        'email' => $user['email'],
        'name' => $user['name'],
      ],
    ];
  }

  public function unshare(
    int $ownerId,
    int $fileId,
    int $userId
  ): void {
    $this->getOwnedFile($ownerId, $fileId);

    if (
      $this->shareRepository->findShare(
        $fileId,
        $userId
      ) === null
    ) {
      throw new InvalidArgumentException(
        'Share not found'
      );
    }

    $this->shareRepository->delete(
      $fileId,
      $userId
    );
  }

  private function getOwnedFile(
    int $ownerId,
    int $fileId
  ): array {
    $file = $this->fileRepository->findById($fileId);

    if (
      $file === null ||
      (int) $file['user_id'] !== $ownerId
    ) {
      throw new RuntimeException(
        'File not found'
      );
    }

    return $file;
  }
}