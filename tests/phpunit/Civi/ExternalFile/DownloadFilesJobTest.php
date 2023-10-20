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
use Civi\ExternalFile\EntityFactory\ExternalFileFactory;
use Civi\ExternalFile\Exception\DownloadAlreadyInProgressException;
use Civi\ExternalFile\Lock\LockFactoryInterface;
use Civi\ExternalFile\Lock\LockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\ExternalFile\DownloadFilesJob
 */
final class DownloadFilesJobTest extends TestCase {

  /**
   * @var \Civi\ExternalFile\ExternalFileDownloaderInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $externalFileDownloaderMock;

  /**
   * @var \Civi\ExternalFile\ExternalFilesDownloadRequiredLoaderInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $externalFilesLoaderMock;

  private DownloadFilesJob $job;

  /**
   * @var \Civi\ExternalFile\Lock\LockFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $lockFactoryMock;

  protected function setUp(): void {
    parent::setUp();
    $this->externalFileDownloaderMock = $this->createMock(ExternalFileDownloaderInterface::class);
    $this->externalFilesLoaderMock = $this->createMock(ExternalFilesDownloadRequiredLoaderInterface::class);
    $this->lockFactoryMock = $this->createMock(LockFactoryInterface::class);
    $this->job = new DownloadFilesJob(
      $this->externalFileDownloaderMock,
      $this->externalFilesLoaderMock,
      $this->lockFactoryMock,
    );
  }

  public function testRun(): void {
    $this->mockLock();

    $externalFile1 = ExternalFileFactory::create(['id' => 1]);
    $externalFile2 = ExternalFileFactory::create(['id' => 2]);

    $this->externalFilesLoaderMock->method('get')->with(3)
      ->willReturn([$externalFile1, $externalFile2]);

    $series = [$externalFile1, $externalFile2];
    $this->externalFileDownloaderMock->expects(static::exactly(2))->method('download')
      ->willReturnCallback(function (ExternalFileEntity $externalFile) use (&$series) {
        static::assertEquals(array_shift($series), $externalFile);
      });

    $this->job->run(3);
  }

  public function testRunIgnoresDownloadAlreadyInProgressException(): void {
    $this->mockLock();

    $externalFile1 = ExternalFileFactory::create(['id' => 1]);
    $externalFile2 = ExternalFileFactory::create(['id' => 2]);
    $this->externalFilesLoaderMock->method('get')->with(4)
      ->willReturn([$externalFile1, $externalFile2]);

    $series = [$externalFile1, $externalFile2];
    $this->externalFileDownloaderMock->expects(static::exactly(2))->method('download')
      ->willReturnCallback(function (ExternalFileEntity $externalFile) use (&$series) {
        static::assertEquals(array_shift($series), $externalFile);

        throw new DownloadAlreadyInProgressException();
      });

    $this->job->run(4);
  }

  public function testRunOnlyOnce(): void {
    $this->mockLock(FALSE);

    $this->externalFilesLoaderMock->expects(static::never())->method('get');
    $this->job->run(3);
  }

  private function mockLock(bool $canLock = TRUE): void {
    $lockMock = $this->createMock(LockInterface::class);
    $lockMock->expects(static::once())->method('tryLock')->willReturn($canLock);
    $this->lockFactoryMock->method('createLock')
      ->willReturn($lockMock);
  }

}
