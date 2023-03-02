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
 * @covers \Civi\ExternalFile\Api4\Query\CompositeCondition
 */
final class CompositeConditionTest extends TestCase {

  public function test(): void {
    $comparisonA = Comparison::new('fieldA', '=', 'valueA');
    $comparisonB = Comparison::new('fieldB', '!=', 123);
    $compositeCondition = CompositeCondition::new('AND', $comparisonA, $comparisonB);

    static::assertSame('AND', $compositeCondition->getOperator());
    static::assertSame([$comparisonA, $comparisonB], $compositeCondition->getConditions());
    static::assertSame(['AND', [$comparisonA->toArray(), $comparisonB->toArray()]], $compositeCondition->toArray());
  }

  public function testFromFieldValuePairs(): void {
    $condition = CompositeCondition::fromFieldValuePairs([
      'fieldA' => 'valueA',
      'fieldB' => 'valueB',
    ], 'OR', '!=');

    static::assertSame('OR', $condition->getOperator());
    static::assertEquals([
      Comparison::new('fieldA', '!=', 'valueA'),
      Comparison::new('fieldB', '!=', 'valueB'),
    ], $condition->getConditions());
  }

}
