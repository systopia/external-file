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
 * @covers \Civi\ExternalFile\Lock\FlockFactory
 */
final class FlockFactoryTest extends TestCase {

  private FlockFactory $flockFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->flockFactory = new FlockFactory();
  }

  public function testCreateLock(): void {
    $flock1 = $this->flockFactory->createLock('test');
    // Returns the same lock object for the same key.
    static::assertSame($flock1, $this->flockFactory->createLock('test'));
    $flock1->tryLock();
    static::assertTrue($flock1->isLocked());

    // Lock is released because FlockFactory only holds weak reference.
    $flock1 = NULL;
    $flock2 = $this->flockFactory->createLock('test');
    static::assertFalse($flock2->isLocked());

    static::assertNotSame($flock2, $this->flockFactory->createLock('another_key'));
  }

}
