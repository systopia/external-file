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

/**
 * Provides a lock using PHP's flock(). Lock is unlocked on destruction.
 *
 * @see \flock()
 */
final class Flock implements LockInterface {

  /**
   * Delete lock file on unlock. Should not be used in combination with blocking
   * lock acquiring.
   */
  public const FLAG_DELETE_FILE_ON_UNLOCK = 1;

  private string $filename;

  private int $flags;

  /**
   * @var resource
   */
  private $handle;

  private bool $locked = FALSE;

  public function __construct(string $filename, int $flags = 0) {
    $this->filename = $filename;
    $this->flags = $flags;
  }

  public function __destruct() {
    $this->unlock();
  }

  public function isLocked(): bool {
    return $this->locked;
  }

  public function lock(): void {
    if (!$this->locked) {
      $this->initHandle();
      $this->locked = flock($this->handle, \LOCK_EX);
      if (!$this->locked) {
        $this->closeHandle();

        throw new \RuntimeException('Failed to acquire lock');
      }
    }
  }

  public function tryLock(): bool {
    if (!$this->locked) {
      $this->initHandle();
      $this->locked = flock($this->handle, \LOCK_EX | \LOCK_NB);
      if (!$this->locked) {
        $this->closeHandle();
      }
    }

    return $this->locked;
  }

  private function closeHandle(): void {
    fclose($this->handle);
    // @phpstan-ignore-next-line
    $this->handle = NULL;
  }

  private function initHandle(): void {
    // @phpstan-ignore-next-line
    if (NULL !== $this->handle) {
      return;
    }

    // Influenced by https://github.com/symfony/symfony/blob/6.3/src/Symfony/Component/Lock/Store/FlockStore.php
    // Silence error reporting
    set_error_handler(function ($type, $msg) use (&$error) {
      $error = $msg;

      return TRUE;
    });
    try {
      // @phpstan-ignore-next-line
      if (!$handle = fopen($this->filename, 'r+') ?: fopen($this->filename, 'r')) {
        // @phpstan-ignore-next-line
        if ($handle = fopen($this->filename, 'x')) {
          chmod($this->filename, 0666);
        }
        // @phpstan-ignore-next-line
        elseif (!$handle = fopen($this->filename, 'r+') ?: fopen($this->filename, 'r')) {
          // Give some time for chmod() to complete
          usleep(100);
          // @phpstan-ignore-next-line
          $handle = fopen($this->filename, 'r+') ?: fopen($this->filename, 'r');
        }
      }
    }
    finally {
      restore_error_handler();
    }

    if (!is_resource($handle)) {
      /** @var string $error */
      throw new \RuntimeException($error);
    }

    $this->handle = $handle;
  }

  private function unlock(): void {
    if ($this->locked) {
      flock($this->handle, \LOCK_UN | \LOCK_NB);
      $this->closeHandle();
      if ((self::FLAG_DELETE_FILE_ON_UNLOCK & $this->flags) !== 0 && file_exists($this->filename)) {
        unlink($this->filename);
      }
    }
  }

}
