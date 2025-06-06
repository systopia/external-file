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

/**
 * @phpstan-type comparisonT array{string, string, 2?: scalar|non-empty-array<scalar>}
 */
final class Comparison implements ConditionInterface {

  private string $field;

  private string $operator;

  /**
   * @phpstan-var scalar|non-empty-array<int, scalar>|null
   */
  private $value;

  /**
   * @param string $operator '=' and '!=' are allowed with NULL as value.
   * @phpstan-param scalar|non-empty-array<int, scalar>|null $value
   *
   * @return self
   */
  public static function new(string $field, string $operator, $value): self {
    return new self($field, $operator, $value);
  }

  /**
   * @param string $operator '=' and '!=' are allowed with NULL as value.
   * @phpstan-param scalar|non-empty-array<int, scalar>|null $value
   */
  public function __construct(string $field, string $operator, $value) {
    $this->field = $field;
    $this->operator = $operator;
    $this->value = $value;
  }

  public function getField(): string {
    return $this->field;
  }

  public function getOperator(): string {
    return $this->operator;
  }

  /**
   * @phpstan-return scalar|non-empty-array<int, scalar>|null
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * @inheritDoc
   *
   * @phpstan-return comparisonT
   */
  public function toArray(): array {
    if (NULL === $this->value) {
      $operator = $this->operator;
      if ('=' === $operator) {
        $operator = 'IS NULL';
      }
      elseif ('!=' === $operator) {
        $operator = 'IS NOT NULL';
      }

      return [$this->field, $operator];
    }

    return [$this->field, $this->operator, $this->value];
  }

}
