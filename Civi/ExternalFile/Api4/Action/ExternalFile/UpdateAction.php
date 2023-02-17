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
use Civi\Api4\Generic\DAOUpdateAction;
use Civi\Api4\Generic\Result;
use Civi\ExternalFile\Api4\Api4Interface;
use Civi\ExternalFile\Api4\Util\FileValueUtil;
use Webmozart\Assert\Assert;

final class UpdateAction extends DAOUpdateAction {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    parent::__construct(ExternalFile::NAME, 'create');
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function _run(Result $result): void {
    $file = $this->updateFile();
    parent::_run($result);

    $record = $result->first();
    foreach ($file as $field => $value) {
      $record['file_' . $field] = $value;
    }
    $result->exchangeArray([$record]);
  }

  /**
   * @phpstan-return array<string, mixed>
   *
   * @throws \CRM_Core_Exception
   */
  private function updateFile(): array {
    $fileValues = FileValueUtil::getFileValues($this->values);
    if ([] !== $fileValues && ['id'] !== array_keys($fileValues)) {
      Assert::integer($fileValues['id'] ?? NULL, '"file_id" is required to update "file_" fields');

      return $this->api4->updateEntity('File', $fileValues['id'], $fileValues)->single();
    }

    return [];
  }

}
