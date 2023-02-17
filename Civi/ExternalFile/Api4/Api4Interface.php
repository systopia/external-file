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

namespace Civi\ExternalFile\Api4;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;

interface Api4Interface {

  /**
   * @phpstan-param array<string, mixed> $params
   *
   * @throws \Civi\API\Exception\NotImplementedException
   */
  public function createAction(string $entityName, string $action, array $params = []): AbstractAction;

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @throws \CRM_Core_Exception
   */
  public function createEntity(string $entityName, array $values): Result;

  /**
   * @return \Civi\Api4\Generic\AbstractGetAction
   *   It's possible that the returned action does not extend this class, but
   *   (most probably) provides the same methods. So the type is not enforced.
   *
   * @throws \Civi\API\Exception\NotImplementedException
   */
  public function createGetAction(string $entityName): AbstractAction;

  /**
   * @throws \CRM_Core_Exception
   */
  public function executeAction(AbstractAction $action): Result;

  /**
   * @phpstan-param array<string, mixed> $params
   *
   * @throws \CRM_Core_Exception
   */
  public function execute(string $entityName, string $actionName, array $params = []): Result;

  /**
   * @throws \CRM_Core_Exception
   */
  public function getEntity(string $entityName, int $id): Result;

  /**
   * @phpstan-param array<string, mixed> $values
   */
  public function getEntityFields(string $entityName, string $action = 'get', array $values = []): Result;

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @throws \CRM_Core_Exception
   */
  public function updateEntity(string $entityName, int $id, array $values): Result;

}
