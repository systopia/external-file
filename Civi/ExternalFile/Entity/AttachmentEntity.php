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

namespace Civi\ExternalFile\Entity;

/**
 * @phpstan-type attachmentT array{
 *   id?: int,
 *   mime_type: string,
 *   description: ?string,
 *   upload_date: string,
 *   entity_table: string,
 *   entity_id: int,
 *   url?: string,
 *   path?: string,
 *   created_id?: ?int,
 * }
 *
 * @phpstan-extends AbstractEntity<attachmentT>
 *
 * @codeCoverageIgnore
 */
final class AttachmentEntity extends AbstractEntity {

  public function getMimeType(): string {
    return $this->values['mime_type'];
  }

  public function getDescription(): ?string {
    return $this->values['description'];
  }

  public function setMimeType(string $mimeType): self {
    $this->values['mime_type'] = $mimeType;

    return $this;
  }

  public function getUploadDate():\DateTimeInterface {
    return new \DateTime($this->values['upload_date']);
  }

  public function getEntityTable(): string {
    return $this->values['entity_table'];
  }

  public function getEntityId(): int {
    return $this->values['entity_id'];
  }

  public function getUrl(): string {
    return $this->values['url'] ?? '';
  }

  public function getPath(): string {
    return $this->values['path'] ?? '';
  }

  public function getCreatedId(): ?int {
    return $this->values['created_id'] ?? NULL;
  }

}
