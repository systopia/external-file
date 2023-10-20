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
use Civi\ExternalFile\EntityFactory\AttachmentFactory;
use Civi\ExternalFile\EntityFactory\ExternalFileFactory;
use Civi\ExternalFile\Event\AuthorizeFileDownloadEvent;
use Civi\ExternalFile\Exception\DownloadAlreadyInProgressException;
use Civi\ExternalFile\ExternalFileDownloaderInterface;
use Civi\ExternalFile\ExternalFileManagerInterface;
use Civi\ExternalFile\ExternalFileStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @covers \Civi\ExternalFile\Controller\FileDownloadController
 */
final class FileDownloadControllerTest extends TestCase {

  /**
   * @var \Civi\ExternalFile\AttachmentManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $attachmentManagerMock;

  private FileDownloadController $controller;

  /**
   * @var \Civi\Core\CiviEventDispatcherInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $eventDispatcherMock;

  /**
   * @var \Civi\ExternalFile\ExternalFileDownloaderInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $externalFileDownloaderMock;

  /**
   * @var \Civi\ExternalFile\ExternalFileManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $externalFileManagerMock;

  /**
   * @var resource
   */
  private $tmpHandle;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
  }

  protected function setUp(): void {
    parent::setUp();
    ClockMock::withClockMock(0);
    $this->attachmentManagerMock = $this->createMock(AttachmentManagerInterface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    $this->externalFileDownloaderMock = $this->createMock(ExternalFileDownloaderInterface::class);
    $this->externalFileManagerMock = $this->createMock(ExternalFileManagerInterface::class);
    $this->controller = new FileDownloadController(
      $this->attachmentManagerMock,
      $this->eventDispatcherMock,
      $this->externalFileDownloaderMock,
      $this->externalFileManagerMock,
    );
  }

  public function testDownload(): void {
    $request = new Request();

    $externalFile = ExternalFileFactory::create(['status' => ExternalFileStatus::NEW]);
    $this->externalFileManagerMock->method('getByIdAndFilename')
      ->with($externalFile->getId(), $externalFile->getFilename())
      ->willReturn($externalFile);

    $attachment = AttachmentFactory::create(['path' => $this->createTempFile()]);
    $this->attachmentManagerMock->method('getByExternalFileId')
      ->with($externalFile->getId())
      ->willReturn($attachment);

    $this->mockAuthorized();

    $this->externalFileDownloaderMock->expects(static::once())->method('download')
      ->with(static::callback(function (ExternalFileEntity $arg) use ($externalFile) {
        static::assertSame($externalFile, $arg);
        $arg->setStatus(ExternalFileStatus::AVAILABLE);

        return TRUE;
      }));

    $response = $this->controller->download($externalFile->getId(), $externalFile->getFilename(), $request);

    static::assertInstanceOf(BinaryFileResponse::class, $response);
    static::assertSame($attachment->getPath(), $response->getFile()->getRealPath());
    static::assertSame(
      HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $externalFile->getFilename()),
      $response->headers->get('Content-Disposition'),
    );
  }

  public function testDownloadAlreadyAvailable(): void {
    $request = new Request();

    $externalFile = ExternalFileFactory::create(['status' => ExternalFileStatus::AVAILABLE]);
    $this->externalFileManagerMock->method('getByIdAndFilename')
      ->with($externalFile->getId(), $externalFile->getFilename())
      ->willReturn($externalFile);

    $attachment = AttachmentFactory::create(['path' => $this->createTempFile()]);
    $this->attachmentManagerMock->method('getByExternalFileId')
      ->with($externalFile->getId())
      ->willReturn($attachment);

    $this->mockAuthorized();

    $this->externalFileDownloaderMock->expects(static::never())->method('download');

    $response = $this->controller->download($externalFile->getId(), $externalFile->getFilename(), $request);

    static::assertInstanceOf(BinaryFileResponse::class, $response);
    static::assertSame($attachment->getPath(), $response->getFile()->getRealPath());
    static::assertSame(
      HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $externalFile->getFilename()),
      $response->headers->get('Content-Disposition'),
    );
  }

  public function testDownloadDownloading(): void {
    $request = new Request();

    $externalFile = ExternalFileFactory::create(['status' => ExternalFileStatus::DOWNLOADING]);
    $this->externalFileManagerMock->method('getByIdAndFilename')
      ->with($externalFile->getId(), $externalFile->getFilename())
      ->willReturn($externalFile);

    $attachment = AttachmentFactory::create(['path' => $this->createTempFile()]);
    $this->attachmentManagerMock->method('getByExternalFileId')
      ->with($externalFile->getId())
      ->willReturn($attachment);

    $this->mockAuthorized();

    $this->externalFileDownloaderMock->expects(static::once())->method('download')
      ->with($externalFile)
      ->willThrowException(new DownloadAlreadyInProgressException());

    $this->externalFileManagerMock->expects(static::once())->method('refresh')
      ->with(static::callback(function (ExternalFileEntity $externalFile) {
        $externalFile->setStatus(ExternalFileStatus::AVAILABLE);

        return TRUE;
      }));

    $response = $this->controller->download($externalFile->getId(), $externalFile->getFilename(), $request);
    static::assertInstanceOf(BinaryFileResponse::class, $response);
    static::assertSame($attachment->getPath(), $response->getFile()->getRealPath());
    static::assertSame(
      HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $externalFile->getFilename()),
      $response->headers->get('Content-Disposition'),
    );

    // 1 second sleep to wait for finishing download.
    static::assertSame(1, time());
  }

  public function testDownloadDownloadingDoesNotFinish(): void {
    $request = new Request();

    $externalFile = ExternalFileFactory::create(['status' => ExternalFileStatus::DOWNLOADING]);
    $this->externalFileManagerMock->method('getByIdAndFilename')
      ->with($externalFile->getId(), $externalFile->getFilename())
      ->willReturn($externalFile);

    $attachment = AttachmentFactory::create();
    $this->attachmentManagerMock->method('getByExternalFileId')
      ->with($externalFile->getId())
      ->willReturn($attachment);

    $this->mockAuthorized();

    $this->externalFileDownloaderMock->expects(static::once())->method('download')
      ->with($externalFile)
      ->willThrowException(new DownloadAlreadyInProgressException());

    $this->externalFileManagerMock->expects(static::exactly(5))->method('refresh')
      ->with($externalFile);

    $response = $this->controller->download($externalFile->getId(), $externalFile->getFilename(), $request);
    static::assertSame(Response::HTTP_GATEWAY_TIMEOUT, $response->getStatusCode());

    // 5 second sleep to wait for finishing download.
    static::assertSame(5, time());
  }

  public function testDownloadDownloadFailed(): void {
    $request = new Request();

    $externalFile = ExternalFileFactory::create(['status' => ExternalFileStatus::NEW]);
    $this->externalFileManagerMock->method('getByIdAndFilename')
      ->with($externalFile->getId(), $externalFile->getFilename())
      ->willReturn($externalFile);

    $attachment = AttachmentFactory::create();
    $this->attachmentManagerMock->method('getByExternalFileId')
      ->with($externalFile->getId())
      ->willReturn($attachment);

    $this->mockAuthorized();

    $this->externalFileDownloaderMock->expects(static::once())->method('download')
      ->with(static::callback(function (ExternalFileEntity $arg) use ($externalFile) {
        static::assertSame($externalFile, $arg);
        $arg->setStatus(ExternalFileStatus::DOWNLOAD_FAILED);

        return TRUE;
      }));

    $response = $this->controller->download($externalFile->getId(), $externalFile->getFilename(), $request);
    static::assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
  }

  public function testDownloadUnauthorized(): void {
    $request = new Request();

    $externalFile = ExternalFileFactory::create(['status' => ExternalFileStatus::NEW]);
    $this->externalFileManagerMock->method('getByIdAndFilename')
      ->with($externalFile->getId(), $externalFile->getFilename())
      ->willReturn($externalFile);

    $attachment = AttachmentFactory::create();
    $this->attachmentManagerMock->method('getByExternalFileId')
      ->with($externalFile->getId())
      ->willReturn($attachment);

    $this->eventDispatcherMock->expects(static::once())->method('dispatch')
      ->with(AuthorizeFileDownloadEvent::class, new AuthorizeFileDownloadEvent($attachment, $externalFile, $request));

    $this->externalFileDownloaderMock->expects(static::never())->method('download');

    $response = $this->controller->download($externalFile->getId(), $externalFile->getFilename(), $request);
    static::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
  }

  public function testDownloadNotFound(): void {
    $request = new Request();

    $this->externalFileManagerMock->method('getByIdAndFilename')
      ->with(123, 'invalid')
      ->willReturn(NULL);

    $this->externalFileDownloaderMock->expects(static::never())->method('download');

    $response = $this->controller->download(123, 'invalid', $request);
    static::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
  }

  public function testDownloadNotModified(): void {
    $lastModified = gmdate(DATE_RFC2822);
    $request = new Request();
    $request->headers->set('If-Modified-Since', $lastModified);

    $externalFile = ExternalFileFactory::create([
      'status' => ExternalFileStatus::AVAILABLE,
      'last_modified' => $lastModified,
    ]);
    $this->externalFileManagerMock->method('getByIdAndFilename')
      ->with($externalFile->getId(), $externalFile->getFilename())
      ->willReturn($externalFile);

    $attachment = AttachmentFactory::create(['path' => $this->createTempFile()]);
    $this->attachmentManagerMock->method('getByExternalFileId')
      ->with($externalFile->getId())
      ->willReturn($attachment);

    $this->mockAuthorized();

    $this->externalFileDownloaderMock->expects(static::never())->method('download');

    $response = $this->controller->download($externalFile->getId(), $externalFile->getFilename(), $request);
    static::assertSame(Response::HTTP_NOT_MODIFIED, $response->getStatusCode());
  }

  public function testDownloadModified(): void {
    $request = new Request();
    $request->headers->set('If-Modified-Since', gmdate(DATE_RFC2822));

    $externalFile = ExternalFileFactory::create([
      'status' => ExternalFileStatus::AVAILABLE,
      'last_modified' => gmdate(DATE_RFC2822, time() + 1),
    ]);
    $this->externalFileManagerMock->method('getByIdAndFilename')
      ->with($externalFile->getId(), $externalFile->getFilename())
      ->willReturn($externalFile);

    $attachment = AttachmentFactory::create(['path' => $this->createTempFile()]);
    $this->attachmentManagerMock->method('getByExternalFileId')
      ->with($externalFile->getId())
      ->willReturn($attachment);

    $this->mockAuthorized();

    $this->externalFileDownloaderMock->expects(static::never())->method('download');

    $response = $this->controller->download($externalFile->getId(), $externalFile->getFilename(), $request);
    static::assertInstanceOf(BinaryFileResponse::class, $response);
    static::assertSame($attachment->getPath(), $response->getFile()->getRealPath());
    static::assertSame(
      HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $externalFile->getFilename()),
      $response->headers->get('Content-Disposition'),
    );
  }

  private function mockAuthorized(): void {
    $this->eventDispatcherMock->expects(static::once())->method('dispatch')
      ->with(
        AuthorizeFileDownloadEvent::class,
        static::callback(function (AuthorizeFileDownloadEvent $authorizeEvent) {
          $authorizeEvent->setAuthorized(TRUE);

          return TRUE;
        })
      );
  }

  private function createTempFile(): string {
    /** @var resource $tmpHandle */
    $tmpHandle = tmpfile();

    // Keep reference so file will not be deleted immediately.
    $this->tmpHandle = $tmpHandle;
    $metaData = stream_get_meta_data($this->tmpHandle);

    return $metaData['uri'];
  }

}
