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

final class ExternalFileStatus {

  public const AVAILABLE = 'available';

  public const DOWNLOAD_FAILED = 'download_failed';

  public const DOWNLOADING = 'downloading';

  public const NEW = 'new';

  public const RELOAD = 'reload';

  /**
   * @phpstan-return array<string, string>
   *
   * @codeCoverageIgnore
   */
  public static function getAll(): array {
    static $status = NULL;
    if (NULL === $status) {
      /** @phpstan-var array<string> $status */
      $status = array_values((new \ReflectionClass(__CLASS__))->getConstants());
      $status = array_combine($status, $status);
    }

    return $status;
  }

}
