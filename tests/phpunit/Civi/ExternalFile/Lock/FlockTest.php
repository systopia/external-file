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

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\ExternalFile\Lock\Flock
 *
 * We'd get issues with CiviCRMs clean up methods because of the fork.
 * @runTestsInSeparateProcesses
 */
final class FlockTest extends TestCase {

  public function test(): void {
    $filename = tempnam(sys_get_temp_dir(), 'flock_test');
    static::assertIsString($filename);

    try {
      $flock = new Flock($filename);
      static::assertFalse($flock->isLocked());
      static::assertTrue($flock->tryLock());
      static::assertTrue($flock->isLocked());
      // Lock can be called again.
      $flock->lock();
      static::assertFalse($this->tryLockInChildProcess($filename, FALSE));

      $flock = new Flock($filename);
      static::assertFalse($flock->isLocked());
      $flock->lock();
      static::assertTrue($flock->isLocked());
      static::assertFalse($this->tryLockInChildProcess($filename, FALSE));

      // Test that flock is released in parent process
      $flock = new Flock($filename);
      static::assertTrue($flock->tryLock());
      $flock = NULL;
      static::assertTrue($this->tryLockInChildProcess($filename, TRUE));
    }
    finally {
      if (file_exists($filename)) {
        unlink($filename);
      }
    }
  }

  public function testWithDelete(): void {
    $filename = tempnam(sys_get_temp_dir(), 'flock_test');
    static::assertIsString($filename);

    try {
      $flock = new Flock($filename, Flock::FLAG_DELETE_FILE_ON_UNLOCK);
      $flock->lock();
      static::assertTrue($flock->isLocked());
      static::assertFalse($this->tryLockInChildProcess($filename, FALSE));
      // Flock is destructed in child process.
      if (function_exists('pcntl_fork')) {
        static::assertFileDoesNotExist($filename);
      }

      // Test that lock file is removed in parent process.
      $flock = new Flock($filename, Flock::FLAG_DELETE_FILE_ON_UNLOCK);
      static::assertTrue($flock->tryLock());
      $flock = NULL;
      static::assertFileDoesNotExist($filename);
    }
    finally {
      if (file_exists($filename)) {
        unlink($filename);
      }
    }
  }

  /**
   * A locked flock will be released when child exits.
   */
  private function tryLockInChildProcess(string $filename, bool $fallback): bool {
    if (!function_exists('pcntl_fork')) {
      $this->addWarning('pcntl_fork is not available, test is incomplete.');

      return $fallback;
    }

    $pid = pcntl_fork();
    if (-1 === $pid) {
      throw new \RuntimeException('Fork failed');
    }

    if (0 === $pid) {
      // Child.
      $flock = new Flock($filename);
      exit($flock->tryLock() ? 2 : 1);
    }

    // Parent.
    pcntl_waitpid($pid, $status);
    static::assertTrue(pcntl_wifexited($status), 'Child exited abnormally');

    return 2 === pcntl_wexitstatus($status);
  }

}
