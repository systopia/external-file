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

namespace Civi\ExternalFile\Api4\Util;

final class FileValueUtil {

  /**
   * Returns all values that have an index prefixed with "file_". The prefix
   * is removed from the index.
   *
   * @phpstan-param array<string, mixed> $values
   *
   * @phpstan-return array<string, mixed>
   */
  public static function getFileValues(array $values): array {
    $fileValues = [];
    foreach ($values as $field => $value) {
      if (str_starts_with($field, 'file_')) {
        $fileValues[substr($field, 5)] = $value;
      }
    }

    return $fileValues;
  }

  /**
   * Returns all values that have an index not prefixed with "file_".
   *
   * @phpstan-param array<string, mixed> $values
   *
   * @phpstan-return array<string, mixed>
   */
  public static function getNonFileValues(array $values): array {
    return array_filter(
      $values,
      fn (string $field) => !str_starts_with($field, 'file_'),
      ARRAY_FILTER_USE_KEY,
    );
  }

}
