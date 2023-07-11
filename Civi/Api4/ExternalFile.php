<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Api4\Generic\AbstractEntity;
use Civi\ExternalFile\Api4\Action\ExternalFile\CreateAction;
use Civi\ExternalFile\Api4\Action\ExternalFile\DeleteAction;
use Civi\ExternalFile\Api4\Action\ExternalFile\GetAction;
use Civi\ExternalFile\Api4\Action\ExternalFile\GetFieldsAction;
use Civi\ExternalFile\Api4\Action\ExternalFile\UpdateAction;

/**
 * ExternalFile entity.
 *
 * Provided by the external_file extension.
 *
 * @package Civi\Api4
 */
class ExternalFile extends AbstractEntity {

  public const NAME = 'ExternalFile';

  /**
   * @inheritDoc
   */
  public static function getFields() {
    return \Civi::service(GetFieldsAction::class);
  }

  public static function create(bool $checkPermissions = TRUE): CreateAction {
    // @phpstan-ignore-next-line
    return \Civi::service(CreateAction::class)->setCheckPermissions($checkPermissions);
  }

  public static function delete(bool $checkPermissions = TRUE): DeleteAction {
    // @phpstan-ignore-next-line
    return \Civi::service(DeleteAction::class)->setCheckPermissions($checkPermissions);
  }

  public static function get(bool $checkPermissions = TRUE): GetAction {
    // @phpstan-ignore-next-line
    return \Civi::service(GetAction::class)->setCheckPermissions($checkPermissions);
  }

  public static function update(bool $checkPermissions = TRUE): UpdateAction {
    // @phpstan-ignore-next-line
    return \Civi::service(UpdateAction::class)->setCheckPermissions($checkPermissions);
  }

  /**
   * @inheritDoc
   *
   * @phpstan-return array<string, array<string|string[]>>
   */
  public static function permissions(): array {
    return File::permissions();
  }

}
