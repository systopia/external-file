<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace phpunit\Civi\ExternalFile;

use Civi\ExternalFile\ExternalFileUriGeneratorInterface;
use Civi\ExternalFile\ExternalFileUriMatcher;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\ExternalFile\ExternalFileUriMatcher
 */
final class ExternalFileUriMatcherTest extends TestCase {

  /**
   * @var \Civi\ExternalFile\ExternalFileUriMatcher
   */
  private ExternalFileUriMatcher $uriMatcher;

  protected function setUp(): void {
    parent::setUp();
    $uriGeneratorMock = $this->createMock(ExternalFileUriGeneratorInterface::class);
    $uriGeneratorMock->method('generateDownloadUri')
      ->willReturnCallback(function (int $externalFileId, string $externalFilename) {
        return sprintf(
          'https://example.org/civicrm/external-file/download/%d/%s',
          $externalFileId,
          rawurlencode($externalFilename)
        );
      });
    $this->uriMatcher = new ExternalFileUriMatcher($uriGeneratorMock);
  }

  public function testMatchDownloadUri(): void {
    static::assertSame(
      [1234, 'test.txt'],
      $this->uriMatcher->matchDownloadUri('https://example.org/civicrm/external-file/download/1234/test.txt')
    );

    static::assertSame(
      [100, 'ä ö ü.ß'],
      $this->uriMatcher->matchDownloadUri(
        'https://example.org/civicrm/external-file/download/100/' . rawurlencode('ä ö ü.ß')
      )
    );

    static::assertNull(
      $this->uriMatcher->matchDownloadUri('https://example.com/civicrm/external-file/download/1234/test.txt')
    );
    static::assertNull(
      $this->uriMatcher->matchDownloadUri('https://example.org/civicrm/external-file/download/1234a/test.txt')
    );
  }

}
