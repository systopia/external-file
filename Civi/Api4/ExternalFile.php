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

  public static function create(): CreateAction {
    return \Civi::service(CreateAction::class);
  }

  public static function delete(): DeleteAction {
    return \Civi::service(DeleteAction::class);
  }

  public static function get(): GetAction {
    return \Civi::service(GetAction::class);
  }

  public static function update(): UpdateAction {
    return \Civi::service(UpdateAction::class);
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
