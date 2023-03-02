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

use Civi\ExternalFile\Api4\Api4;
use Civi\ExternalFile\Api4\DAOActionFactory;
use Civi\ExternalFile\Fixtures\ExternalFileFixture;

/**
 * @covers \Civi\ExternalFile\ExternalFilesDownloadRequiredLoader
 *
 * @group headless
 */
final class ExternalFilesDownloadRequiredLoaderTest extends AbstractExternalFileHeadlessTestCase {

  private ExternalFilesDownloadRequiredLoader $loader;

  protected function setUp(): void {
    parent::setUp();
    $externalFileManager = new ExternalFileManager(new Api4(), new DAOActionFactory());
    $this->loader = new ExternalFilesDownloadRequiredLoader($externalFileManager);
  }

  /**
   * @dataProvider provideDownloadRequiredStatus
   */
  public function testGetRequired(string $status): void {
    $externalFile = ExternalFileFixture::addFixture(['status' => $status]);
    static::assertEquals([$externalFile], $this->getExternalFiles(0));
  }

  /**
   * @phpstan-return iterable<array{string}>
   */
  public function provideDownloadRequiredStatus(): iterable {
    yield [ExternalFileStatus::NEW];
    yield [ExternalFileStatus::RELOAD];
  }

  /**
   * @dataProvider provideDownloadNotRequiredStatus
   */
  public function testGetNotRequired(string $status): void {
    ExternalFileFixture::addFixture(['status' => $status]);
    static::assertSame([], $this->getExternalFiles(0));
  }

  /**
   * @phpstan-return iterable<array{string}>
   */
  public function provideDownloadNotRequiredStatus(): iterable {
    yield [ExternalFileStatus::DOWNLOAD_FAILED];
    yield [ExternalFileStatus::AVAILABLE];
  }

  public function testDownloadFailed(): void {
    // Download tried one hour ago.
    ExternalFileFixture::addFixture([
      'status' => ExternalFileStatus::DOWNLOAD_FAILED,
      'download_start_date' => date('Y-m-d H:i:s', time() - 3600),
      'download_try_count' => 1,
    ]);
    // Exceeds failed count.
    ExternalFileFixture::addFixture([
      'status' => ExternalFileStatus::DOWNLOAD_FAILED,
      'download_start_date' => '1970-01-01 00:00:00',
      'download_try_count' => 2,
    ]);
    // Download tried two hours ago.
    $externalFile = ExternalFileFixture::addFixture([
      'status' => ExternalFileStatus::DOWNLOAD_FAILED,
      'download_start_date' => date('Y-m-d H:i:s', time() - 3600 * 2),
      'download_try_count' => 1,
    ]);
    static::assertEquals([$externalFile], $this->getExternalFiles(1));
  }

  public function testDownloading(): void {
    // Download recently started.
    ExternalFileFixture::addFixture([
      'status' => ExternalFileStatus::DOWNLOADING,
      'download_start_date' => date('Y-m-d H:i:00'),
      'download_try_count' => 1,
    ]);
    // Download started an hour ago.
    $externalFile = ExternalFileFixture::addFixture([
      'status' => ExternalFileStatus::DOWNLOADING,
      'download_start_date' => date('Y-m-d H:i:s', time() - 3600),
      'download_try_count' => 1,
    ]);
    static::assertEquals([$externalFile], $this->getExternalFiles(0));
  }

  /**
   * @return array<\Civi\ExternalFile\Entity\ExternalFileEntity>
   */
  private function getExternalFiles(int $retries): array {
    // @phpstan-ignore-next-line
    return iterator_to_array($this->loader->get($retries));
  }

}
