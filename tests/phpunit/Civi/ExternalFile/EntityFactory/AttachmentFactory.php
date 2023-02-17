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

namespace Civi\ExternalFile\EntityFactory;

use Civi\ExternalFile\Entity\AttachmentEntity;

/**
 * @phpstan-type attachmentT array{
 *   id?: int,
 *   mime_type?: string,
 *   description?: ?string,
 *   upload_date?: string,
 *   entity_table?: string,
 *   entity_id?: int,
 *   url?: string,
 *   path?: string,
 *   created_id?: ?int,
 * }
 */
final class AttachmentFactory {

  public const DEFAULT_ID = 44;

  /**
   * @phpstan-param attachmentT $values
   */
  public static function create(array $values = []): AttachmentEntity {
    return AttachmentEntity::fromArray($values + [
      'id' => self::DEFAULT_ID,
      'mime_type' => 'application/octet-stream',
      'description' => NULL,
      'upload_date' => '2023-01-02 03:04:05',
      'entity_table' => 'civicrm_external_file',
      'entity_id' => ExternalFileFactory::DEFAULT_ID,
      'path' => '/path/to/file',
    ]);
  }

}
