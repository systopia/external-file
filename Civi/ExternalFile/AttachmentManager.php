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
use Civi\ExternalFile\Api4\Util\Uuid;
use Civi\ExternalFile\Entity\AttachmentEntity;
use Civi\ExternalFile\Entity\ExternalFileEntity;

final class AttachmentManager implements AttachmentManagerInterface {

  private Api3Interface $api3;

  private Api4Interface $api4;

  private bool $commitDeleteCallbackRegistered = FALSE;

  private DAOActionFactoryInterface $daoActionFactory;

  /**
   * @phpstan-var array<int>
   */
  private array $preDeletedExternalFileIds = [];

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
      'mime_type' => $mimeType ?? 'inode/x-empty',
      'description' => $description,
      'created_id' => $createdId,
      'sequential' => 1,
      // Ensure path is returned.
      'check_permissions' => FALSE,
    ];

    $result = $this->api3->execute('Attachment', 'create', $values);
    // @phpstan-ignore-next-line
    $attachment = AttachmentEntity::fromApi3Values($result['values'][0]);

    if (\CRM_Core_Transaction::isActive()) {
      \CRM_Core_Transaction::addCallback(
        \CRM_Core_Transaction::PHASE_PRE_ROLLBACK,
        fn() => file_exists($attachment->getPath()) && @unlink($attachment->getPath()),
      );
    }

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
    if (\CRM_Core_Transaction::isActive()) {
      // Executed on post commit so files on file system will only be deleted on
      // successful transaction.
      $this->registerCommitDeleteCallback();
      $this->preDeletedExternalFileIds[] = $externalFileId;
      // Deletion of ExternalFile is cascaded. We change the identifier, so it
      // can be reused before actual deletion.
      $this->api4->updateEntity(
        'ExternalFile',
        $externalFileId,
        ['identifier' => Uuid::generateRandom()],
        ['checkPermissions' => FALSE],
      );
    }
    else {
      $this->doDeleteByExternalFileId($externalFileId);
    }
  }

  /**
   * @inheritDoc
   */
  public function getByExternalFileId(int $externalFileId): ?AttachmentEntity {
    $result = $this->api3->execute('Attachment', 'get', [
      'entity_table' => 'civicrm_external_file',
      'entity_id' => $externalFileId,
      'sequential' => 1,
      // Ensure path is returned.
      'check_permissions' => FALSE,
    ]);

    // @phpstan-ignore-next-line
    return 1 === $result['count'] ? AttachmentEntity::fromApi3Values($result['values'][0]) : NULL;
  }

  /**
   * @inheritDoc
   */
  public  function getPreDeletedExternalFileIds(): array {
    return $this->preDeletedExternalFileIds;
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
    $result = $this->api3->execute('Attachment', 'create', [
      'id' => $externalFile->getFileId(),
      'entity_table' => 'civicrm_external_file',
      'entity_id' => $externalFile->getId(),
      'content' => $content,
      'sequential' => 1,
      // Ensure path is returned.
      'check_permissions' => FALSE,
    ]);

    // @phpstan-ignore-next-line
    return AttachmentEntity::fromApi3Values($result['values'][0]);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function doDeleteByExternalFileId(int $externalFileId): void {
    $this->api3->execute('Attachment', 'delete', [
      'entity_table' => 'civicrm_external_file',
      'entity_id' => $externalFileId,
    ]);
  }

  /**
   * @internal
   *
   * @throws \CRM_Core_Exception
   */
  public function deleteOnPostCommit(): void {
    $this->commitDeleteCallbackRegistered = FALSE;
    foreach ($this->preDeletedExternalFileIds as $id) {
      $this->doDeleteByExternalFileId($id);
    }

    $this->preDeletedExternalFileIds = [];
  }

  private function registerCommitDeleteCallback(): void {
    if (!$this->commitDeleteCallbackRegistered) {
      \CRM_Core_Transaction::addCallback(
      \CRM_Core_Transaction::PHASE_PRE_ROLLBACK,
        function () {
          $this->preDeletedExternalFileIds = [];
          $this->commitDeleteCallbackRegistered = FALSE;
        },
      );

      \CRM_Core_Transaction::addCallback(
        \CRM_Core_Transaction::PHASE_POST_COMMIT,
        [$this, 'deleteOnPostCommit'],
      );

      $this->commitDeleteCallbackRegistered = TRUE;
    }
  }

}
