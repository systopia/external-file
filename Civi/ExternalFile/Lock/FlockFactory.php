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

namespace Civi\ExternalFile\Lock;

final class FlockFactory implements LockFactoryInterface {

  /**
   * @phpstan-var array<\WeakReference<Flock>>
   */
  private array $weakRefs = [];

  public function createLock(string $key): LockInterface {
    $key = $this->sanitizeKey($key);
    if (isset($this->weakRefs[$key])) {
      $lock = $this->weakRefs[$key]->get();
      if (NULL !== $lock) {
        return $lock;
      }
    }

    $lock = new Flock($this->getFilename($key), Flock::FLAG_DELETE_FILE_ON_UNLOCK);
    $this->weakRefs[$key] = \WeakReference::create($lock);

    return $lock;
  }

  private function getFilename(string $key): string {
    return sys_get_temp_dir() . '/' . $key . '.lock';
  }

  private function sanitizeKey(string $key): string {
    // @phpstan-ignore-next-line
    return preg_replace('/[^\w\-~_\.]+/u', '-', $key);
  }

}
