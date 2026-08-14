<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\ExternalFile;

final class ExternalFileUriMatcher implements ExternalFileUriMatcherInterface {

  public function __construct(
    private readonly ExternalFileUriGeneratorInterface $uriGenerator
  ) {}

  public function matchDownloadUri(string $uri): ?array {
    $tmpUri = $this->uriGenerator->generateDownloadUri(99999, 'match_file_name');
    $pattern = '#^' . str_replace(
      ['99999', 'match_file_name'],
      ['([1-9][0-9]*)', '([^/]+)'],
      preg_quote($tmpUri)
    ) . '$#';
    if (1 === preg_match($pattern, $uri, $matches)) {
      return [
        (int) $matches[1],
        rawurldecode($matches[2]),
      ];
    }

    return NULL;
  }

}
