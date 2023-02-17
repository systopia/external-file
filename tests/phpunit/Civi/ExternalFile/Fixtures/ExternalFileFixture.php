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
use Civi\ExternalFile\Entity\ExternalFileEntity;
use Civi\ExternalFile\ExternalFileStatus;

final class ExternalFileFixture {

  /**
   * @phpstan-param array<string, mixed> $values
   */
  public static function addFixture(array $values = []): ExternalFileEntity {
    $action = new DAOCreateAction('ExternalFile', 'create');
    $action->setValues($values + [
      'source' => 'https://example.org/test.txt',
      'filename' => 'test.txt',
      'status' => ExternalFileStatus::NEW,
      'download_start_date' => NULL,
      'download_try_count' => 0,
      'extension' => 'test',
      'custom_data' => NULL,
      'last_modified' => NULL,
    ]);
    $result = $action->execute();

    // @phpstan-ignore-next-line
    return ExternalFileEntity::fromArray($result->single());
  }

}
