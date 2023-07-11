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

use Civi\ExternalFile\Entity\AttachmentEntity;
use Civi\ExternalFile\Entity\ExternalFileEntity;

interface AttachmentManagerInterface {

  /**
   * @throws \CRM_Core_Exception
   */
  public function create(
    ExternalFileEntity $externalFile,
    ?string $description,
    ?int $createdId,
    ?string $mimeType
  ): AttachmentEntity;

  /**
   * If a transaction is running the entity will be deleted on commit. So the
   * file on the file system is only deleted if the transaction succeeds.
   *
   * @throws \CRM_Core_Exception
   *
   * @see getPreDeletedExternalFileIds()
   */
  public function deleteByExternalFileId(int $externalFileId): void;

  /**
   * @throws \CRM_Core_Exception
   */
  public function getByExternalFileId(int $externalFileId): ?AttachmentEntity;

  /**
   * @phpstan-return array<int>
   *   The external file IDs planned for deletion on transaction commit.
   *
   * @see deleteByExternalFileId()
   */
  public function getPreDeletedExternalFileIds(): array;

  /**
   * @throws \CRM_Core_Exception
   */
  public function update(AttachmentEntity $attachment): void;

  /**
   * @throws \CRM_Core_Exception
   */
  public function writeContent(ExternalFileEntity $externalFile, string $content): AttachmentEntity;

}
