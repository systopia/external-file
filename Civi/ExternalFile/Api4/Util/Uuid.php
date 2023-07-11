<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

/**
 * @codeCoverageIgnore
 */
final class Uuid {

  /**
   * Copied from https://github.com/symfony/polyfill-uuid/blob/a41886c1c81dc075a09c71fe6db5b9d68c79de23/Uuid.php#L356
   */
  public static function generateRandom(): string {
    $uuid = bin2hex(random_bytes(16));

    // phpcs:disable Squiz.PHP.CommentedOutCode.Found
    return sprintf('%08s-%04s-4%03s-%04x-%012s',
      // 32 bits for "time_low"
      substr($uuid, 0, 8),
      // 16 bits for "time_mid"
      substr($uuid, 8, 4),
      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      substr($uuid, 13, 3),
      // 16 bits:
      // * 8 bits for "clk_seq_hi_res",
      // * 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      hexdec(substr($uuid, 16, 4)) & 0x3fff | 0x8000,
      // 48 bits for "node"
      substr($uuid, 20, 12)
    );
    // phpcs:enable
  }

}
