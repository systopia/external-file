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
use Civi\ExternalFile\EntityFactory\AttachmentFactory;
use Civi\ExternalFile\EntityFactory\ExternalFileFactory;
use Civi\ExternalFile\Exception\DownloadAlreadyInProgressException;
use Civi\ExternalFile\Lock\LockFactoryInterface;
use Civi\ExternalFile\Lock\LockInterface;
use CRM_Utils_HttpClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * @covers \Civi\ExternalFile\ExternalFileDownloader
 */
final class ExternalFileDownloaderTest extends TestCase {

  /**
   * @var AttachmentManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $attachmentManagerMock;

  private ExternalFileDownloader $downloader;

  /**
   * @var ExternalFileManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $externalFileManagerMock;

  /**
   * @var \CRM_Utils_HttpClient&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $httpClientMock;

  /**
   * @var \Civi\ExternalFile\Lock\LockFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $lockFactoryMock;

  private TestLogger $logger;

  /**
   * @var \Symfony\Component\Mime\MimeTypeGuesserInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $mimeTypeGuesserMock;

  protected function setUp(): void {
    parent::setUp();
    $this->attachmentManagerMock = $this->createMock(AttachmentManagerInterface::class);
    $this->externalFileManagerMock = $this->createMock(ExternalFileManagerInterface::class);
    $this->httpClientMock = $this->createMock(CRM_Utils_HttpClient::class);
    $this->lockFactoryMock = $this->createMock(LockFactoryInterface::class);
    $this->logger = new TestLogger();
    $this->mimeTypeGuesserMock = $this->createMock(MimeTypeGuesserInterface::class);
    $this->downloader = new ExternalFileDownloader(
      $this->attachmentManagerMock,
      $this->externalFileManagerMock,
      $this->httpClientMock,
      $this->lockFactoryMock,
      $this->logger,
      $this->mimeTypeGuesserMock,
    );
  }

  public function testDownload(): void {
    $this->mockLock();
    $externalFile = ExternalFileFactory::create();
    $attachment = AttachmentFactory::create();

    $this->externalFileManagerMock->expects(static::exactly(2))->method('update')
      ->withConsecutive(
        [
          static::callback(function (ExternalFileEntity $externalFile) {
            // Expectations are revalidated on test finish, so the assertion doesn't match than, anymore.
            static $called = FALSE;
            if (!$called) {
              static::assertSame(1, $externalFile->getDownloadTryCount());
              static::assertSame(ExternalFileStatus::DOWNLOADING, $externalFile->getStatus());
              static::assertNotNull($externalFile->getDownloadStartDate());
              $called = TRUE;
            }

            return TRUE;
          }),
        ],
        [
          static::callback(function (ExternalFileEntity $externalFile) {
            static::assertSame(ExternalFileStatus::AVAILABLE, $externalFile->getStatus());
            static::assertNotNull($externalFile->getLastModified());

            return TRUE;
          }),
        ],
      );

    $this->httpClientMock->expects(static::once())->method('get')
      ->with($externalFile->getSource())
      ->willReturn([CRM_Utils_HttpClient::STATUS_OK, 'test']);

    $this->attachmentManagerMock->expects(static::once())->method('writeContent')
      ->with($externalFile, 'test')
      ->willReturn($attachment);

    $this->mimeTypeGuesserMock->expects(static::once())->method('guessMimeType')
      ->with($attachment->getPath())
      ->willReturn('new/type');

    $this->attachmentManagerMock->expects(static::once())->method('update')
      ->with($attachment);

    $this->downloader->download($externalFile);

    static::assertSame('new/type', $attachment->getMimeType());
  }

  public function testDownloadError(): void {
    $this->mockLock();
    $externalFile = ExternalFileFactory::create();

    $this->externalFileManagerMock->expects(static::exactly(2))->method('update')
      ->withConsecutive(
        [
          static::callback(function (ExternalFileEntity $externalFile) {
            // Expectations are revalidated on test finish, so the assertion doesn't match than, anymore.
            static $called = FALSE;
            if (!$called) {
              static::assertSame(1, $externalFile->getDownloadTryCount());
              static::assertSame(ExternalFileStatus::DOWNLOADING, $externalFile->getStatus());
              static::assertNotNull($externalFile->getDownloadStartDate());
              $called = TRUE;
            }

            return TRUE;
          }),
        ],
        [
          static::callback(function (ExternalFileEntity $externalFile) {
            static::assertSame(ExternalFileStatus::DOWNLOAD_FAILED, $externalFile->getStatus());
            static::assertNull($externalFile->getLastModified());

            return TRUE;
          }),
        ],
      );

    $this->httpClientMock->expects(static::once())->method('get')
      ->with($externalFile->getSource())
      ->willReturn([CRM_Utils_HttpClient::STATUS_DL_ERROR, 'something']);

    $this->attachmentManagerMock->expects(static::never())->method('writeContent');

    $this->downloader->download($externalFile);

    static::assertTrue($this->logger->hasError([
      'message' => sprintf('Downloading "%s" failed.', $externalFile->getSource()),
      'context' => ['external_file_id' => $externalFile->getId()],
    ]));
  }

  public function testDownloadException(): void {
    $this->mockLock();
    $externalFile = ExternalFileFactory::create();

    $this->externalFileManagerMock->expects(static::exactly(2))->method('update')
      ->withConsecutive(
        [
          static::callback(function (ExternalFileEntity $externalFile) {
            // Expectations are revalidated on test finish, so the assertion doesn't match than, anymore.
            static $called = FALSE;
            if (!$called) {
              static::assertSame(1, $externalFile->getDownloadTryCount());
              static::assertSame(ExternalFileStatus::DOWNLOADING, $externalFile->getStatus());
              static::assertNotNull($externalFile->getDownloadStartDate());
              $called = TRUE;
            }

            return TRUE;
          }),
        ],
        [
          static::callback(function (ExternalFileEntity $externalFile) {
            static::assertSame(ExternalFileStatus::DOWNLOAD_FAILED, $externalFile->getStatus());
            static::assertNull($externalFile->getLastModified());

            return TRUE;
          }),
        ],
      );

    $downloadException = new \RuntimeException('some error message');
    $this->httpClientMock->expects(static::once())->method('get')
      ->with($externalFile->getSource())
      ->willThrowException($downloadException);

    $this->attachmentManagerMock->expects(static::never())->method('writeContent');

    $e = NULL;
    try {
      $this->downloader->download($externalFile);
    }
    catch (\Exception $e) {
      // @ignoreException
    }
    static::assertSame($downloadException, $e);

    static::assertTrue($this->logger->hasError([
      'message' => sprintf('Downloading "%s" failed: some error message', $externalFile->getSource()),
      'context' => [
        'exception' => $downloadException,
        'external_file_id' => $externalFile->getId(),
      ],
    ]));
  }

  public function testDownloadAlreadyDownloading(): void {
    $this->mockLock(FALSE);
    $externalFile = ExternalFileFactory::create();

    $this->externalFileManagerMock->expects(static::never())->method('update');

    $this->expectException(DownloadAlreadyInProgressException::class);
    $this->expectExceptionMessage('Download is already in progress');
    $this->downloader->download($externalFile);

  }

  private function mockLock(bool $canLock = TRUE): void {
    $lockMock = $this->createMock(LockInterface::class);
    $lockMock->expects(static::once())->method('tryLock')->willReturn($canLock);
    $this->lockFactoryMock->method('createLock')
      ->willReturn($lockMock);
  }

}
