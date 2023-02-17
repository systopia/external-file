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
 * @phpstan-type externalFileT array{
 *   id?: int,
 *   file_id?: int,
 *   source: string,
 *   filename: string,
 *   extension: string,
 *   custom_data: ?array<mixed>,
 *   status: string,
 *   download_start_date: ?string,
 *   download_try_count: int,
 *   last_modified: ?string,
 * }
 *
 * @phpstan-extends AbstractEntity<externalFileT>
 *
 * @codeCoverageIgnore
 */
final class ExternalFileEntity extends AbstractEntity {

  /**
   * @return int ID of referenced File entity. -1 during creation.
   */
  public function getFileId(): int {
    return $this->values['file_id'] ?? -1;
  }

  public function setFileId(int $fileId): self {
    $this->values['file_id'] = $fileId;

    return $this;
  }

  public function getSource(): string {
    return $this->values['source'];
  }

  public function getFilename(): string {
    return $this->values['filename'];
  }

  public function getExtension(): string {
    return $this->values['extension'];
  }

  /**
   * @phpstan-return array<mixed>|null JSON serializable array or NULL.
   */
  public function getCustomData(): ?array {
    return $this->values['custom_data'];
  }

  public function getStatus(): string {
    return $this->values['status'];
  }

  public function setStatus(string $status): self {
    $this->values['status'] = $status;

    return $this;
  }

  public function getDownloadStartDate(): ?\DateTimeInterface {
    return static::toDateTimeOrNull($this->values['download_start_date']);
  }

  public function setDownloadStartDate(?\DateTimeInterface $downloadStartDate): self {
    $this->values['download_start_date'] = static::toDateTimeStrOrNull($downloadStartDate);

    return $this;
  }

  public function getDownloadTryCount(): int {
    return $this->values['download_try_count'];
  }

  public function incDownloadTryCount(): self {
    ++$this->values['download_try_count'];

    return $this;
  }

  public function getLastModified(): ?string {
    return $this->values['last_modified'];
  }

  public function setLastModified(string $lastModified): self {
    $this->values['last_modified'] = $lastModified;

    return $this;
  }

}
