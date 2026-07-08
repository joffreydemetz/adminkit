<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\AdminKit\List;

/**
 * Resolves the effective filter state for a list from its layered sources.
 *
 * Precedence (lowest to highest): defaults < session-stored < request `f_*`
 * params < forced values. The CSRF token, page and default ordering are then
 * injected. The consuming framework supplies the raw request/session values;
 * this class holds no HTTP or session coupling.
 *
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class FilterStateResolver
{
  /**
   * Pull the `f_`-prefixed filter params out of a raw request query array,
   * stripping the prefix.
   *
   * @param array<string,mixed> $query
   * @return array<string,mixed>
   */
  public static function extractFilterParams(array $query): array
  {
    $params = [];
    foreach ($query as $key => $value) {
      if ('f_' === substr($key, 0, 2)) {
        $params[substr($key, 2)] = $value;
      }
    }
    return $params;
  }

  /**
   * Merge the filter-state layers and inject token / page / default ordering.
   *
   * @param array<string,mixed> $defaults
   * @param array<string,mixed> $stored
   * @param array<string,mixed> $queryParams  already `f_`-stripped
   * @param array<string,mixed> $force
   * @param mixed               $queryPage    raw ?page= value, or null when absent
   * @return array<string,mixed>
   */
  public static function resolve(
    array $defaults,
    array $stored,
    array $queryParams,
    array $force,
    string $token,
    mixed $queryPage,
    string $defaultOrderBy
  ): array {
    $data = array_merge($defaults, $stored, $queryParams, $force);

    $data['t'] = $token;
    $data['page'] = $queryPage ?? ($data['page'] ?? 1);

    if (empty($data['orderBy'])) {
      $data['orderBy'] = $defaultOrderBy;
    }

    return $data;
  }
}
