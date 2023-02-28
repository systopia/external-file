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

use Civi\ExternalFile\Api3\Api3Interface;
use Civi\ExternalFile\Api4\Api4Interface;
use Civi\ExternalFile\Api4\DAOActionFactoryInterface;
use Civi\ExternalFile\Entity\AttachmentEntity;
use Civi\ExternalFile\Entity\ExternalFileEntity;

final class AttachmentManager implements AttachmentManagerInterface {

  private Api3Interface $api3;

  private Api4Interface $api4;

  private DAOActionFactoryInterface $daoActionFactory;

  public function __construct(
    Api3Interface $api3,
    Api4Interface $api4,
    DAOActionFactoryInterface $daoActionFactory
  ) {
    $this->api3 = $api3;
    $this->api4 = $api4;
    $this->daoActionFactory = $daoActionFactory;
  }

  /**
   * @inheritDoc
   */
  public function create(
    ExternalFileEntity $externalFile,
    ?string $description,
    ?int $createdId,
    ?string $mimeType
  ): AttachmentEntity {
    $values = [
      'entity_table' => 'civicrm_external_file',
      'entity_id' => $externalFile->getId(),
      'name' => $externalFile->getFilename(),
      // content is required by Attachment entity.
      'content' => '',
      // mime_type is required by Attachment entity.
      'mime_type' => $mimeType ?? 'application/octet-stream',
      'description' => $description,
      'createdId' => $createdId,
      // Ensure path is returned.
      'check_permissions' => FALSE,
    ];

    /** @phpstan-var array{count: int, id: int, values: array<int, array<string, mixed>>} $result */
    $result = $this->api3->execute('Attachment', 'create', $values);
    $attachment = $this->createAttachmentFromResult($result);

    $externalFile->setFileId($attachment->getId());
    $externalFileUpdateAction = $this->daoActionFactory->update('ExternalFile')
      ->setCheckPermissions(FALSE)
      ->addWhere('id', '=', $externalFile->getId())
      ->addValue('file_id', $externalFile->getFileId());
    $this->api4->executeAction($externalFileUpdateAction);

    return $attachment;
  }

  /**
   * @inheritDoc
   */
  public function deleteByExternalFileId(int $externalFileId): void {
    $this->api3->execute('Attachment', 'delete', [
      'entity_table' => 'civicrm_external_file',
      'entity_id' => $externalFileId,
    ]);
  }

  /**
   * @inheritDoc
   */
  public function getByExternalFileId(int $externalFileId): ?AttachmentEntity {
    /** @phpstan-var array{count: int, id: int, values: array<int, array<string, mixed>>} $result */
    $result = $this->api3->execute('Attachment', 'get', [
      'entity_table' => 'civicrm_external_file',
      'entity_id' => $externalFileId,
      // Ensure path is returned.
      'check_permissions' => FALSE,
    ]);

    return 1 === $result['count'] ? $this->createAttachmentFromResult($result) : NULL;
  }

  /**
   * @inheritDoc
   */
  public function update(AttachmentEntity $attachment): void {
    // Attachment API has no update action, so we have to directly use File.
    $this->api4->updateEntity(
      'File',
      $attachment->getId(),
      $attachment->toArray(),
      ['checkPermissions' => FALSE],
    );
  }

  /**
   * @inheritDoc
   */
  public function writeContent(ExternalFileEntity $externalFile, string $content): AttachmentEntity {
    // Even though the action is named "create" the underling File entity won't
    // be changed in this case.
    /** @phpstan-var array{id: int, values: array<int, array<string, mixed>>} $result */
    $result = $this->api3->execute('Attachment', 'create', [
      'id' => $externalFile->getFileId(),
      'entity_table' => 'civicrm_external_file',
      'entity_id' => $externalFile->getId(),
      'content' => $content,
      // Ensure path is returned.
      'check_permissions' => FALSE,
    ]);

    return $this->createAttachmentFromResult($result);
  }

  /**
   * @phpstan-param array{id: int, values: array<int, array<string, mixed>>} $result
   */
  private function createAttachmentFromResult(array $result): AttachmentEntity {
    $values = $result['values'][$result['id']];
    if ('' === $values['description']) {
      // Empty string is returned, when description is NULL.
      $values['description'] = NULL;
    }
    // Integers are returned as strings.
    // @phpstan-ignore-next-line
    $values['id'] = (int) $values['id'];
    // @phpstan-ignore-next-line
    $values['entity_id'] = (int) $values['entity_id'];
    // @phpstan-ignore-next-line
    $values['created_id'] = (int) $values['created_id'];
    if (0 === $values['created_id']) {
      $values['created_id'] = NULL;
    }

    // @phpstan-ignore-next-line
    return AttachmentEntity::fromArray($values);
  }

}
