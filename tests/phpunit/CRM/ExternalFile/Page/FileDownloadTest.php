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

use Civi\Api4\ExternalFile;
use Civi\ExternalFile\AbstractExternalFileHeadlessTestCase;
use Civi\ExternalFile\Api3\Api3;
use Civi\ExternalFile\Api4\Api4;
use Civi\ExternalFile\Api4\DAOActionFactory;
use Civi\ExternalFile\AttachmentManager;
use Civi\ExternalFile\Entity\ExternalFileEntity;
use Civi\ExternalFile\Event\AuthorizeFileDownloadEvent;
use Civi\ExternalFile\ExternalFileStatus;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \CRM_ExternalFile_Page_FileDownload
 *
 * @group headless
 */
class CRM_ExternalFile_Page_FileDownloadTest extends AbstractExternalFileHeadlessTestCase {

  private AttachmentManager $attachmentManager;

  protected function setUp(): void {
    parent::setUp();
    $this->attachmentManager = new AttachmentManager(new Api3(), new Api4(), new DAOActionFactory());
  }

  public function test(): void {
    $page = $this->createPage('civicrm/external-file/download/1/test.txt');
    $response = $page->getResponse();
    static::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

    $page = $this->createPage('civicrm/external-file/download/no-int/test.txt');
    $response = $page->getResponse();
    static::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

    $page = $this->createPage('civicrm/external-file/download');
    $response = $page->getResponse();
    static::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

    // Test with real file.
    // @phpstan-ignore-next-line
    $externalFile = ExternalFileEntity::fromArray(ExternalFile::create()->setValues([
      'source' => 'https://example.org/test.txt',
      'extension' => 'test',
      'status' => ExternalFileStatus::AVAILABLE,
    ])->execute()->single());
    $this->attachmentManager->writeContent($externalFile, 'test');

    try {
      $page = $this->createPage('civicrm/external-file/download/' . $externalFile->getId() . '/test.txt');
      $response = $page->getResponse();
      static::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());

      // Mock authorization.
      \Civi::dispatcher()->addListener(
        AuthorizeFileDownloadEvent::class,
        fn (AuthorizeFileDownloadEvent $event) => $event->setAuthorized(TRUE)
      );

      $response = $page->getResponse();
      static::assertSame(Response::HTTP_OK, $response->getStatusCode());

      ob_start();
      $response->sendContent();
      $content = ob_get_contents();
      ob_end_clean();
      static::assertSame('test', $content);
    }
    finally {
      // Ensure file is removed from filesystem.
      $this->attachmentManager->deleteByExternalFileId($externalFile->getId());
    }
  }

  private function createPage(string $path): CRM_ExternalFile_Page_FileDownload {
    $page = new CRM_ExternalFile_Page_FileDownload();
    $page->urlPath = explode('/', $path);

    return $page;
  }

}
