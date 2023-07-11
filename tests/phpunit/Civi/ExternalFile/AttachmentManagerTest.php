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

use Civi\Api4\File;
use Civi\ExternalFile\Api3\Api3;
use Civi\ExternalFile\Api4\Api4;
use Civi\ExternalFile\Api4\DAOActionFactory;
use Civi\ExternalFile\Fixtures\ExternalFileFixture;

/**
 * @covers \Civi\ExternalFile\AttachmentManager
 *
 * @group headless
 */
final class AttachmentManagerTest extends AbstractExternalFileHeadlessTestCase {

  private AttachmentManager $attachmentManager;

  protected function setUp(): void {
    parent::setUp();
    $this->attachmentManager = new AttachmentManager(new Api3(), new Api4(), new DAOActionFactory());
  }

  public function test(): void {
    $externalFile = ExternalFileFixture::addFixture();

    $attachment = $this->attachmentManager->create($externalFile, 'description', NULL, NULL);
    static::assertFileExists($attachment->getPath());
    try {
      static::assertSame($attachment->getId(), $externalFile->getFileId());
      static::assertSame($externalFile->getId(), $attachment->getEntityId());
      static::assertSame('civicrm_external_file', $attachment->getEntityTable());
      static::assertSame('description', $attachment->getDescription());
      static::assertNull($attachment->getCreatedId());

      $attachment->setMimeType('foo/bar');
      $this->attachmentManager->update($attachment);
      $fileValues = File::get()->addWhere('id', '=', $attachment->getId())->execute()->single();
      static::assertSame('foo/bar', $fileValues['mime_type']);

      static::assertNull($this->attachmentManager->getByExternalFileId($externalFile->getId() + 1));

      // Hash in "url" depends on system time, so it's excluded from assert.
      static::assertEquals(
        ['url' => NULL] + $attachment->toArray(),
        // @phpstan-ignore-next-line
        ['url' => NULL] + $this->attachmentManager->getByExternalFileId($externalFile->getId())->toArray()
      );

      static::assertEquals(
        ['url' => NULL] + $attachment->toArray(),
        ['url' => NULL] + $this->attachmentManager->writeContent($externalFile, 'test')->toArray()
      );
      static::assertSame('test', file_get_contents($attachment->getPath()));

      $this->attachmentManager->deleteByExternalFileId($externalFile->getId());
      static::assertFileExists($attachment->getPath());
      static::assertCount(1, File::get()->addWhere('id', '=', $attachment->getId())->execute());
      $this->attachmentManager->deleteOnPostCommit();
      static::assertFileDoesNotExist($attachment->getPath());
      static::assertCount(0, File::get()->addWhere('id', '=', $attachment->getId())->execute());
    }
    finally {
      if (file_exists($attachment->getPath())) {
        unlink($attachment->getPath());
      }
    }
  }

}
