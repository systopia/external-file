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

namespace Civi\ExternalFile\Fixtures;

use Civi\Api4\Generic\DAOCreateAction;
use Civi\ExternalFile\Api4\Util\Uuid;
use Civi\ExternalFile\Entity\ExternalFileEntity;
use Civi\ExternalFile\ExternalFileStatus;

final class ExternalFileFixture {

  /**
   * @phpstan-param array<string, mixed> $values
   */
  public static function addFixture(array $values = []): ExternalFileEntity {
    $action = new DAOCreateAction('ExternalFile', 'create');
    $action->setValues($values + [
      'file_id' => NULL,
      'source' => 'https://example.org/test.txt',
      'filename' => 'test.txt',
      'status' => ExternalFileStatus::NEW,
      'download_start_date' => NULL,
      'download_try_count' => 0,
      'extension' => 'test',
      'identifier' => Uuid::generateRandom(),
      'custom_data' => NULL,
      'last_modified' => NULL,
      'file_mime_type' => 'inode/x-empty',
    ]);
    $values = $action->execute()->single();
    // Filter values added by CiviCRM in create.
    unset($values['custom']);
    unset($values['check_permissions']);
    unset($values['file_mime_type']);

    // @phpstan-ignore-next-line
    $externalFile = ExternalFileEntity::fromArray($values);
    // Reformat download start date.
    $externalFile->setDownloadStartDate($externalFile->getDownloadStartDate());

    return $externalFile;
  }

}
