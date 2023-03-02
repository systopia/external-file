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
use Civi\Api4\Generic\DAODeleteAction;
use Civi\ExternalFile\AttachmentManagerInterface;

final class DeleteAction extends DAODeleteAction {

  private AttachmentManagerInterface $attachmentManager;

  public function __construct(AttachmentManagerInterface $attachmentManager) {
    parent::__construct(ExternalFile::NAME, 'delete');
    $this->attachmentManager = $attachmentManager;
  }

  /**
   * @phpstan-param array<array{id: int}> $items
   *
   * @phpstan-return array<array{id: int}>
   *
   * @throws \CRM_Core_Exception
   */
  protected function deleteObjects($items): array {
    $result = [];
    foreach ($items as $item) {
      // Deletion of File entity is cascaded, so we do not need to delete ExternalFile entity explicitly.
      $this->attachmentManager->deleteByExternalFileId($item['id']);
      $result[] = ['id' => $item['id']];
    }

    return $result;
  }

}
