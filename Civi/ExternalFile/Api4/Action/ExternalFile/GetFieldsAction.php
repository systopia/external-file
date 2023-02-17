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

namespace Civi\ExternalFile\Api4\Action\ExternalFile;

use Civi\Api4\ExternalFile;
use Civi\Api4\Generic\BasicGetFieldsAction;
use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\ExternalFile\Api4\Api4Interface;

/**
 * @phpstan-type fieldT array<string, array<string, scalar>|scalar[]|scalar|null>&array{name: string}
 */
final class GetFieldsAction extends BasicGetFieldsAction {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    parent::__construct(ExternalFile::NAME, 'getFields');
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   *
   * @phpstan-return array<fieldT>
   *
   * @throws \CRM_Core_Exception
   *
   * @noinspection PhpMissingParentCallCommonInspection
   *
   */
  protected function getRecords(): array {
    $fileFields = $this->api4->getEntityFields('File', $this->action);
    $action = (new DAOGetFieldsAction($this->getEntityName(), $this->getActionName()))
      ->setAction($this->action)
      ->setValues($this->values)
      ->setSelect($this->select)
      ->setOrderBy($this->orderBy)
      ->setLoadOptions($this->loadOptions);
    if ('create' === $this->action) {
      $action->addWhere('name', '!=', 'file_id');
    }
    $externalFileFields = $this->api4->executeAction($action);
    /** @phpstan-var array<fieldT> $fields */
    $fields = $externalFileFields->getArrayCopy();

    if ('create' === $this->action) {
      foreach ($fields as &$field) {
        if (in_array($field['name'], ['filename', 'download_try_count', 'status'], TRUE)) {
          $field['required'] = FALSE;
        }
      }
    }

    $fields[] = [
      'type' => 'Extra',
      'entity' => 'ExternalFile',
      'required' => FALSE,
      'nullable' => FALSE,
      'readonly' => TRUE,
      'name' => 'uri',
      'title' => 'URI',
      'description' => 'URI to load the file',
      'data_type' => 'String',
      'label' => 'URI',
      'operators' => [],
    ];

    /**
     * @phpstan-var fieldT $fileField
     */
    foreach ($fileFields as $fileField) {
      if ((
        'create' !== $this->action || in_array($fileField['name'], ['mime_type', 'description', 'created_id'], TRUE)
        ) &&'id' !== $fileField['name']
      ) {
        $fileField['name'] = 'file_' . $fileField['name'];
        $fileField['type'] = 'Extra';
        $fields[] = $fileField;
      }
    }

    return $fields;
  }

}
