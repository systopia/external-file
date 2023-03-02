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

namespace Civi\ExternalFile\Api4\Query;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\ExternalFile\Api4\Query\Comparison
 */
final class ComparisonTest extends TestCase {

  public function testEquals(): void {
    $comparison = Comparison::new('field', '=', 'value');
    static::assertSame('field', $comparison->getField());
    static::assertSame('=', $comparison->getOperator());
    static::assertSame('value', $comparison->getValue());
    static::assertSame(['field', '=', 'value'], $comparison->toArray());
  }

  public function testEqualsNull(): void {
    $comparison = Comparison::new('field', '=', NULL);
    static::assertSame(['field', 'IS NULL'], $comparison->toArray());
  }

  public function testNotEqualsNull(): void {
    $comparison = Comparison::new('field', '!=', NULL);
    static::assertSame(['field', 'IS NOT NULL'], $comparison->toArray());
  }

}
