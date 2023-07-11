<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\ExternalFile\Controller;

use Civi\Core\CiviEventDispatcherInterface;
use Civi\ExternalFile\AttachmentManagerInterface;
use Civi\ExternalFile\Entity\ExternalFileEntity;
use Civi\ExternalFile\Event\AuthorizeFileDownloadEvent;
use Civi\ExternalFile\Exception\DownloadAlreadyInProgressException;
use Civi\ExternalFile\ExternalFileDownloaderInterface;
use Civi\ExternalFile\ExternalFileManagerInterface;
use Civi\ExternalFile\ExternalFileStatus;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Webmozart\Assert\Assert;

final class FileDownloadController {

  private const DOWNLOAD_WAIT_LIMIT_SECONDS = 5;

  private AttachmentManagerInterface $attachmentManager;

  private CiviEventDispatcherInterface $eventDispatcher;

  private ExternalFileDownloaderInterface $externalFileDownloader;

  private ExternalFileManagerInterface $externalFileManager;

  public function __construct(
    AttachmentManagerInterface $attachmentManager,
    CiviEventDispatcherInterface $eventDispatcher,
    ExternalFileDownloaderInterface $externalFileDownloader,
    ExternalFileManagerInterface $externalFileManager
  ) {
    $this->attachmentManager = $attachmentManager;
    $this->eventDispatcher = $eventDispatcher;
    $this->externalFileDownloader = $externalFileDownloader;
    $this->externalFileManager = $externalFileManager;
  }

  public function download(int $externalFileId, string $filename, Request $request): Response {
    $externalFile = $this->externalFileManager->getByIdAndFilename($externalFileId, $filename);
    if (NULL === $externalFile) {
      return new Response(Response::$statusTexts[Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
    }

    $attachment = $this->attachmentManager->getByExternalFileId($externalFile->getId());
    Assert::notNull($attachment);
    $event = new AuthorizeFileDownloadEvent($attachment, $externalFile, $request);
    $this->eventDispatcher->dispatch(AuthorizeFileDownloadEvent::class, $event);

    if (!$event->isAuthorized()) {
      return new Response(Response::$statusTexts[Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
    }

    if (ExternalFileStatus::AVAILABLE !== $externalFile->getStatus()) {
      try {
        $this->externalFileDownloader->download($externalFile);
      }
      catch (DownloadAlreadyInProgressException $e) {
        $this->waitForFinishDownload($externalFile);
        if (ExternalFileStatus::DOWNLOADING === $externalFile->getStatus()) {
          return new Response(
            Response::$statusTexts[Response::HTTP_GATEWAY_TIMEOUT],
            Response::HTTP_GATEWAY_TIMEOUT
          );
        }
      }
    }

    if (ExternalFileStatus::AVAILABLE !== $externalFile->getStatus()) {
      return new Response(
        Response::$statusTexts[Response::HTTP_SERVICE_UNAVAILABLE],
        Response::HTTP_SERVICE_UNAVAILABLE
      );
    }

    $headers = [
      'Content-Type' => $attachment->getMimeType(),
      'Last-Modified' => $externalFile->getLastModified(),
    ];

    if ($this->isCacheUsable($request, $externalFile)) {
      return new Response('', Response::HTTP_NOT_MODIFIED, $headers);
    }

    return (new BinaryFileResponse(
      $attachment->getPath(),
      Response::HTTP_OK,
      $headers,
      FALSE,
    ))->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $externalFile->getFilename());
  }

  private function isCacheUsable(Request $request, ExternalFileEntity $externalFile): bool {
    $ifModifiedSince = $request->headers->getDate('If-Modified-Since');
    if (NULL === $ifModifiedSince || NULL === $externalFile->getLastModified()) {
      return FALSE;
    }

    $lastModified = \DateTime::createFromFormat(DATE_RFC2822, $externalFile->getLastModified());
    Assert::notFalse(
      $lastModified,
      sprintf('The last modified date "%s" is not parseable.', $externalFile->getLastModified())
    );

    return $ifModifiedSince == $lastModified;
  }

  private function waitForFinishDownload(ExternalFileEntity $externalFile): void {
    for ($i = 0; $i < self::DOWNLOAD_WAIT_LIMIT_SECONDS; ++$i) {
      sleep(1);
      $this->externalFileManager->refresh($externalFile);

      if (ExternalFileStatus::DOWNLOADING !== $externalFile->getStatus()) {
        break;
      }
    }
  }

}
