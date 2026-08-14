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

namespace Civi\ExternalFile\Api4\Action\ExternalFile;

use Civi\Api4\ExternalFile;
use Civi\Api4\Generic\DAOCreateAction;
use Civi\Api4\Generic\Result;
use Civi\ExternalFile\Api4\Util\FileValueUtil;
use Civi\ExternalFile\Api4\Util\Uuid;
use Civi\ExternalFile\AttachmentManagerInterface;
use Civi\ExternalFile\Controller\FileDownloadController;
use Civi\ExternalFile\Entity\ExternalFileEntity;
use Civi\ExternalFile\Exception\DownloadFailedException;
use Civi\ExternalFile\ExternalFileStatus;
use Civi\ExternalFile\ExternalFileUriGeneratorInterface;
use Civi\ExternalFile\ExternalFileUriMatcherInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

final class CreateAction extends DAOCreateAction {

  public function __construct(
    private readonly AttachmentManagerInterface $attachmentManager,
    private readonly FileDownloadController $fileDownloadController,
    private readonly ExternalFileUriGeneratorInterface $uriGenerator,
    private readonly ExternalFileUriMatcherInterface $uriMatcher,
  ) {
    parent::__construct(ExternalFile::NAME, 'create');
  }

  public function _run(Result $result): void {
    Assert::string($this->values['source'] ?? NULL, 'Field "source" is missing.');
    $content = $this->getContent($this->values['source']);
    $externalFile = $this->createExternalFileEntity();

    $attachment = $this->attachmentManager->create(
      $externalFile,
      $this->values['file_description'] ?? NULL,
      $this->values['file_created_id'] ?? NULL,
      $this->values['file_mime_type'] ?? NULL,
      $content,
    );

    $resultValues = $externalFile->toArray();
    $resultValues['file_file_type_id'] = NULL;
    $resultValues['file_document'] = NULL;
    foreach ($attachment->toArray() as $field => $value) {
      // Filter extra fields from Attachment.
      if ('path' === $field) {
        $resultValues['file_uri'] = basename($value);
      }
      elseif (!in_array($field, ['entity_id', 'entity_table', 'name', 'url', 'icon'], TRUE)) {
        $resultValues['file_' . $field] = $value;
      }
    }

    $resultValues['uri'] = $this->uriGenerator->generateDownloadUri(
      $externalFile->getId(),
      $externalFile->getFilename(),
    );

    $result->exchangeArray([$resultValues]);
  }

  private function createExternalFileEntity(): ExternalFileEntity {
    $values = $this->values;
    $this->values = FileValueUtil::getNonFileValues($values);
    Assert::string($this->values['source'] ?? NULL, 'Field "source" is missing.');

    $this->values['filename'] ??= rawurldecode(basename($this->values['source']));
    Assert::notEmpty($this->values['filename']);
    $this->values['identifier'] ??= Uuid::generateRandom();
    $this->values['download_try_count'] ??= 0;
    $this->values['status'] ??= ExternalFileStatus::NEW;
    $this->values['download_start_date'] ??= NULL;
    $this->values['custom_data'] ??= NULL;
    $this->values['last_modified'] ??= NULL;
    $this->values['file_mime_type'] ??= 'inode/x-empty';

    $result = new Result();
    parent::_run($result);
    $this->values = $values;

    return ExternalFileEntity::singleFromApiResult($result);
  }

  private function getContent(string $source): ?string {
    [$existingId, $existingFilename] = $this->uriMatcher->matchDownloadUri($source) ?? [NULL, NULL];
    if (NULL === $existingId || NULL === $existingFilename) {
      // Content will be fetched later from source URI.
      return NULL;
    }

    /*
     * source is a URI to a file already managed by this extension. We
     * immediately fetch it to make sure the user adding the file is authorized
     * to access the source file. Cookies and headers from the request might
     * be used for authorization.
     */
    // @phpstan-ignore-next-line Ignore use of superglobals
    $request = Request::create($source, cookies: $_COOKIE, server: $_SERVER);
    // Avoid cache being used.
    $request->headers->remove('If-Modified-Since');
    $downloadResponse = $this->fileDownloadController->download($existingId, $existingFilename, $request);
    if (!$downloadResponse->isSuccessful()) {
      throw new DownloadFailedException(
        sprintf('Downloading %s failed with status code %d', $source, $downloadResponse->getStatusCode()),
        $downloadResponse->getStatusCode()
      );
    }

    $this->values['file_mime_type'] = $downloadResponse->headers->get('Content-Type');
    $this->values['last_modified'] = $downloadResponse->headers->get('Last-Modified');
    $this->values['status'] = ExternalFileStatus::AVAILABLE;

    assert($downloadResponse instanceof BinaryFileResponse);

    return $downloadResponse->getFile()->getContent();
  }

}
