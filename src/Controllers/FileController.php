<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\DirectoryService;
use InvalidArgumentException;
use Throwable;
use App\Services\FileService;

class FileController
{
  private AuthService $authService;
  private DirectoryService $directoryService;
  private FileService $fileService;

  public function __construct(App $app)
  {
    /** @var AuthService $authService */
    $authService = $app->getService('auth');

    /** @var DirectoryService $directoryService */
    $directoryService = $app->getService('directory');

    /** @var FileService $fileService */
    $fileService = $app->getService('file');

    $this->fileService = $fileService;
    $this->authService = $authService;
    $this->directoryService = $directoryService;
  }

  public function addDirectory(Request $request): Response
  {
    try {
      $user = $this->authService->getUser($request);

      $directory = $this->directoryService->create(
        (int) $user['id'],
        $request->getData()
      );

      return (new Response())
        ->setStatusCode(201)
        ->setData([
          'message' => 'Directory created successfully',
          'directory' => $directory,
        ]);
    } catch (InvalidArgumentException $exception) {
      return (new Response())
        ->setStatusCode(400)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(404)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }

  public function getDirectory(
    Request $request,
    array $parameters
  ): Response {
    try {
      $user = $this->authService->getUser($request);

      $directory = $this->directoryService->getWithFiles(
        (int) $user['id'],
        (int) ($parameters['id'] ?? 0)
      );

      return (new Response())
        ->setData([
          'directory' => $directory,
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(404)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }

  public function renameDirectory(Request $request): Response
  {
    try {
      $user = $this->authService->getUser($request);

      $directory = $this->directoryService->rename(
        (int) $user['id'],
        $request->getData()
      );

      return (new Response())
        ->setData([
          'message' => 'Directory renamed successfully',
          'directory' => $directory,
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(400)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }

  public function deleteDirectory(
    Request $request,
    array $parameters
  ): Response {
    try {
      $user = $this->authService->getUser($request);

      $this->directoryService->delete(
        (int) $user['id'],
        (int) ($parameters['id'] ?? 0)
      );

      return (new Response())
        ->setData([
          'message' => 'Directory deleted successfully',
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(404)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }

  public function addFile(Request $request): Response
  {
    try {
      $user = $this->authService->getUser($request);

      $files = $request->getFiles();

      if (!isset($files['file'])) {
        throw new InvalidArgumentException(
          'File is required'
        );
      }

      $data = $request->getData();

      $directoryId = isset($data['directory_id'])
        && $data['directory_id'] !== ''
        ? (int) $data['directory_id']
        : null;

      $file = $this->fileService->upload(
        (int) $user['id'],
        $files['file'],
        $directoryId
      );

      return (new Response())
        ->setStatusCode(201)
        ->setData([
          'message' => 'File uploaded successfully',
          'file' => $file,
        ]);
    } catch (InvalidArgumentException $exception) {
      return (new Response())
        ->setStatusCode(400)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(500)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }

  public function listFiles(Request $request): Response
  {
    try {
      $user = $this->authService->getUser($request);

      $files = $this->fileService->getList(
        (int) $user['id']
      );

      return (new Response())
        ->setData([
          'files' => $files,
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(401)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }

  public function getFile(
    Request $request,
    array $parameters
  ): Response {
    try {
      $user = $this->authService->getUser($request);

      $file = $this->fileService->get(
        (int) $user['id'],
        (int) ($parameters['id'] ?? 0)
      );

      return (new Response())
        ->setData([
          'file' => $file,
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(404)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }

  public function renameFile(Request $request): Response
  {
    try {
      $user = $this->authService->getUser($request);

      $file = $this->fileService->rename(
        (int) $user['id'],
        $request->getData()
      );

      return (new Response())
        ->setData([
          'message' => 'File renamed successfully',
          'file' => $file,
        ]);
    } catch (InvalidArgumentException $exception) {
      return (new Response())
        ->setStatusCode(400)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(404)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }

  public function removeFile(
    Request $request,
    array $parameters
  ): Response {
    try {
      $user = $this->authService->getUser($request);

      $this->fileService->delete(
        (int) $user['id'],
        (int) ($parameters['id'] ?? 0)
      );

      return (new Response())
        ->setData([
          'message' => 'File deleted successfully',
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(404)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }

  public function moveFile(Request $request): Response
  {
    try {
      $user = $this->authService->getUser($request);

      $file = $this->fileService->move(
        (int) $user['id'],
        $request->getData()
      );

      return (new Response())
        ->setData([
          'message' => 'File moved successfully',
          'file' => $file,
        ]);
    } catch (Throwable $exception) {
      return (new Response())
        ->setStatusCode(400)
        ->setData([
          'error' => $exception->getMessage(),
        ]);
    }
  }
}