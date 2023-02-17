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

namespace Civi\ExternalFile\Entity;

use Civi\Api4\Generic\Result;
use Civi\ExternalFile\Util\DateTimeUtil;

/**
 * Wrapper class for the entity arrays returned by CiviCRM API. This class
 * requires that the entity has a primary key named "id" of type unsigned int.
 * "check_permissions" and "custom" are automatically added by CiviCRM since
 * version 5.53.
 *
 * @template T of array<string, mixed>
 *
 * T should contain `id?: int`, and optionally
 * `check_permissions?: bool, custom?: mixed`
 * }
 *
 * @phpstan-consistent-constructor
 *
 * @codeCoverageIgnore
 */
abstract class AbstractEntity {

  /**
   * @phpstan-var T
   */
  protected array $values;

  /**
   * @phpstan-return array<int|string, static>
   *   The keys of the given result are preserved.
   */
  public static function allFromApiResult(Result $result): array {
    // @phpstan-ignore-next-line
    return \array_map(fn (array $record) => static::fromArray($record), $result->getArrayCopy());
  }

  /**
   * @phpstan-param T $values
   *
   * @return static
   */
  public static function fromArray(array $values): self {
    return new static($values);
  }

  /**
   * @phpstan-param T $values
   */
  public function __construct(array $values) {
    $this->values = $values;
  }

  /**
   * @param string $key
   * @param mixed $default
   *
   * @return mixed
   */
  public function get(string $key, $default = NULL) {
    return $this->values[$key] ?? $default;
  }

  /**
   * @return int Returns -1 for a new, unpersisted entity.
   */
  public function getId(): int {
    /** @phpstan-ignore-next-line  */
    return $this->values['id'] ?? -1;
  }

  public function isNew(): bool {
    return -1 === $this->getId();
  }

  /**
   * @phpstan-param T $values
   *
   * @internal
   */
  public function setValues(array $values): void {
    $this->values = $values;
  }

  /**
   * @phpstan-return T
   */
  public function toArray(): array {
    return $this->values;
  }

  protected static function toDateTimeOrNull(?string $dateTimeStr): ?\DateTime {
    return DateTimeUtil::toDateTimeOrNull($dateTimeStr);
  }

  protected static function toDateTimeStr(\DateTimeInterface $dateTime): string {
    return DateTimeUtil::toDateTimeStr($dateTime);
  }

  protected static function toDateTimeStrOrNull(?\DateTimeInterface $dateTime): ?string {
    return DateTimeUtil::toDateTimeStrOrNull($dateTime);
  }

}
