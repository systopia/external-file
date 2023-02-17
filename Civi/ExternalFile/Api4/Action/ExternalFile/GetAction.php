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
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\Result;
use Civi\ExternalFile\ExternalFileUriGeneratorInterface;

final class GetAction extends DAOGetAction {

  private ExternalFileUriGeneratorInterface $uriGenerator;

  public function __construct(ExternalFileUriGeneratorInterface $uriGenerator) {
    parent::__construct(ExternalFile::NAME, 'get');
    $this->uriGenerator = $uriGenerator;
  }

  public function _run(Result $result): void {
    // @phpstan-ignore-next-line
    $this->where = array_map(fn (array $where) => $this->fixWhere($where), $this->where);
    $this->orderBy = $this->fixOrderBy($this->orderBy);
    $this->select = array_map(fn (string $field) => preg_replace('/^file_/', 'file.', $field), $this->select);
    if ([] === $this->select) {
      $this->addSelect('*', 'file.*');
    }
    elseif (in_array('*', $this->select, TRUE) && !in_array('file.*', $this->select, TRUE)) {
      $this->addSelect('file.*');
    }

    $this->addJoin('File AS file', 'INNER', NULL, ['file.id', '=', 'file_id']);
    parent::_run($result);

    $newRecords = [];
    /** @phpstan-var array<string, mixed> $record */
    foreach ($result->getArrayCopy() as $record) {
      $newRecord = [];
      foreach ($record as $field => $value) {
        $newRecord[preg_replace('/^file\./', 'file_', $field)] = $value;
      }
      if (is_int($newRecord['id'] ?? NULL) && is_string($newRecord['filename'] ?? NULL)) {
        $newRecord['uri'] = $this->uriGenerator->generateDownloadUri($newRecord['id'], $newRecord['filename']);
      }
      $newRecords[] = $newRecord;
    }

    $result->exchangeArray($newRecords);
  }

  /**
   * @phpstan-param array{string, string|array<array<mixed>>, 2?: mixed} $where
   *
   * @phpstan-return array{string, string|array<array<mixed>>, 2?: mixed}
   */
  private function fixWhere(array $where): array {
    if (2 === count($where) && is_array($where[1])) {
      // @phpstan-ignore-next-line
      $where[1] = array_map(fn(array $subWhere) => $this->fixWhere($subWhere), $where[1]);
    }
    elseif ('file_id' !== $where[0]) {
      $where[0] = preg_replace('/^file_/', 'file.', $where[0]);
    }

    // @phpstan-ignore-next-line
    return $where;
  }

  /**
   * @phpstan-param array<string, 'ASC'|'DESC'> $orderBy
   *
   * @phpstan-return array<string, 'ASC'|'DESC'>
   */
  private function fixOrderBy(array $orderBy): array {
    $result = [];
    foreach ($orderBy as $field => $order) {
      $result[preg_replace('/^file_/', 'file.', $field)] = $order;
    }

    return $result;
  }

}
