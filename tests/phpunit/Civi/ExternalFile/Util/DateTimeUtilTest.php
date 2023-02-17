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

namespace Civi\ExternalFile\Util;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\ExternalFile\Util\DateTimeUtil
 */
final class DateTimeUtilTest extends TestCase {

  public function testToDateTimeOrNull(): void {
    static::assertEquals(new \DateTime('2022-10-04 01:02:03'), DateTimeUtil::toDateTimeOrNull('2022-10-04 01:02:03'));
    static::assertNull(DateTimeUtil::toDateTimeOrNull(NULL));
  }

  public function testToDateTimeStr(): void {
    static::assertSame('2022-10-04 01:02:00', DateTimeUtil::toDateTimeStr(new \DateTime('2022-10-04 01:02')));
  }

  public function testToDateTimeStrOrNull(): void {
    static::assertSame('2022-10-04 01:02:00', DateTimeUtil::toDateTimeStrOrNull(new \DateTime('2022-10-04 01:02')));
    static::assertNull(DateTimeUtil::toDateTimeStrOrNull(NULL));
  }

}
