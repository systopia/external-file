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

namespace Civi\ExternalFile;

use Civi\ExternalFile\Entity\ExternalFileEntity;
use Civi\ExternalFile\Exception\DownloadAlreadyInProgressException;
use Civi\ExternalFile\Lock\LockFactoryInterface;
use CRM_Utils_HttpClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

final class ExternalFileDownloader implements ExternalFileDownloaderInterface {

  private AttachmentManagerInterface $attachmentManager;

  private ExternalFileManagerInterface $externalFileManager;

  private CRM_Utils_HttpClient $httpClient;

  private LockFactoryInterface $lockFactory;

  private LoggerInterface $logger;

  private MimeTypeGuesserInterface $mimeTypeGuesser;

  public function __construct(
    AttachmentManagerInterface $attachmentManager,
    ExternalFileManagerInterface $externalFileManager,
    CRM_Utils_HttpClient $httpClient,
    LockFactoryInterface $lockFactory,
    LoggerInterface $logger,
    MimeTypeGuesserInterface $mimeTypeGuesser
  ) {
    $this->attachmentManager = $attachmentManager;
    $this->externalFileManager = $externalFileManager;
    $this->httpClient = $httpClient;
    $this->lockFactory = $lockFactory;
    $this->logger = $logger;
    $this->mimeTypeGuesser = $mimeTypeGuesser;
  }

  /**
   * @inheritDoc
   */
  public function download(ExternalFileEntity $externalFile): void {
    $lock = $this->lockFactory->createLock('civicrm.external-file.download.' . $externalFile->getId());
    if (!$lock->tryLock()) {
      throw new DownloadAlreadyInProgressException('Download is already in progress');
    }

    $this->logger->info(sprintf('Start downloading "%s".', $externalFile->getSource()), [
      'external_file_id' => $externalFile->getId(),
    ]);
    $externalFile
      ->setStatus(ExternalFileStatus::DOWNLOADING)
      ->setDownloadStartDate(new \DateTime(date('Y-m-d H:i:s')))
      ->incDownloadTryCount();
    $this->externalFileManager->update($externalFile);

    try {
      $lastModified = gmdate(DATE_RFC2822);
      [$status, $content] = $this->httpClient->get($externalFile->getSource());

      if ($this->httpClient::STATUS_OK === $status) {
        $attachment = $this->attachmentManager->writeContent($externalFile, $content);
        $mimeType = $this->mimeTypeGuesser->guessMimeType($attachment->getPath()) ?? 'application/octet-stream';
        if ($attachment->getMimeType() !== $mimeType) {
          $attachment->setMimeType($mimeType);
          $this->attachmentManager->update($attachment);
        }

        $externalFile->setLastModified($lastModified);
        $externalFile->setStatus(ExternalFileStatus::AVAILABLE);
      }
      else {
        $this->logger->error(
          sprintf('Downloading "%s" failed.', $externalFile->getSource()),
          ['external_file_id' => $externalFile->getId()],
        );
        $externalFile->setStatus(ExternalFileStatus::DOWNLOAD_FAILED);
      }

      $this->externalFileManager->update($externalFile);
    }
    catch (\Throwable $e) {
      $this->logger->error(
        sprintf('Downloading "%s" failed: %s', $externalFile->getSource(), $e->getMessage()),
        [
          'exception' => $e,
          'external_file_id' => $externalFile->getId(),
        ],
      );
      $externalFile->setStatus(ExternalFileStatus::DOWNLOAD_FAILED);
      $this->externalFileManager->update($externalFile);

      throw $e;
    }
  }

}
