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

final class CompositeCondition implements ConditionInterface {

  private string $operator;

  /**
   * @phpstan-var array<ConditionInterface>
   */
  private array $conditions;

  /**
   * @phpstan-param array<string, scalar|non-empty-array<int, scalar>|null> $fieldValuePairs
   *
   * @return static
   */
  public static function fromFieldValuePairs(
    array $fieldValuePairs,
    string $compositeOperation = 'AND',
    string $operator = '='
  ): self {
    $conditions = [];
    foreach ($fieldValuePairs as $key => $value) {
      $conditions[] = Comparison::new($key, $operator, $value);
    }

    return new self($compositeOperation, ...$conditions);
  }

  public static function new(string $operation, ConditionInterface ...$conditions): self {
    return new self($operation, ...$conditions);
  }

  public function __construct(string $operation, ConditionInterface ...$conditions) {
    $this->operator = $operation;
    $this->conditions = $conditions;
  }

  /**
   * @phpstan-return array<ConditionInterface>
   */
  public function getConditions(): array {
    return $this->conditions;
  }

  public function getOperator(): string {
    return $this->operator;
  }

  /**
   * @inheritDoc
   */
  public function toArray(): array {
    $conditions = array_map(fn (ConditionInterface $condition) => $condition->toArray(), $this->conditions);

    return [$this->operator, $conditions];
  }

}
