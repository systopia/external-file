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
use Civi\ExternalFile\AttachmentManagerInterface;
use Civi\ExternalFile\Entity\ExternalFileEntity;
use Civi\ExternalFile\ExternalFileStatus;
use Civi\ExternalFile\ExternalFileUriGeneratorInterface;
use Webmozart\Assert\Assert;

final class CreateAction extends DAOCreateAction {

  private AttachmentManagerInterface $attachmentManager;

  private ExternalFileUriGeneratorInterface $uriGenerator;

  public function __construct(
    AttachmentManagerInterface $attachmentManager,
    ExternalFileUriGeneratorInterface $uriGenerator
  ) {
    parent::__construct(ExternalFile::NAME, 'create');
    $this->attachmentManager = $attachmentManager;
    $this->uriGenerator = $uriGenerator;
  }

  public function _run(Result $result): void {
    $externalFile = $this->createExternalFileEntity();
    $attachment = $this->attachmentManager->create(
      $externalFile,
      $this->values['file_description'] ?? NULL,
        $this->values['file_created_id'] ?? NULL,
        $this->values['file_mime_type'] ?? NULL,
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

    $this->values['filename'] ??= basename($this->values['source']);
    Assert::notEmpty($this->values['filename']);
    $this->values['download_try_count'] ??= 0;
    $this->values['status'] ??= ExternalFileStatus::NEW;
    $this->values['download_start_date'] ??= NULL;
    $this->values['custom_data'] ??= NULL;
    $this->values['last_modified'] ??= NULL;

    $result = new Result();
    parent::_run($result);
    $externalFileValues = $result->single();
    $this->values = $values;

    // @phpstan-ignore-next-line
    return ExternalFileEntity::fromArray($externalFileValues);
  }

}
