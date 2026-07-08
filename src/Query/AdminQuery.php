<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\AdminKit\Query;

use JDZ\Database\Query\SelectQuery;

/**
 * SelectQuery with admin-list conveniences: category / version / keyword
 * SELECT joins and published / category / keyword WHERE filters.
 *
 * Column and table names come from the calling model (not user input), so the
 * interpolated identifiers here carry no injection surface; bound values use
 * placeholders.
 *
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class AdminQuery extends SelectQuery
{
  // --- SELECT helpers (add columns/joins) ---

  public function selectCategory(string $emptyQuoted): static
  {
    return $this
      ->select('a.id_category')
      ->select('COALESCE(c.title, ' . $emptyQuoted . ') AS category')
      ->join('left', '#__category AS c ON c.id = a.id_category');
  }

  public function selectVersion(): static
  {
    return $this->select('a.version');
  }

  public function selectKeywords(string $table, string $foreignKey, string $separatorQuoted): static
  {
    $sub = (new SelectQuery())
      ->select('s_p.id AS ' . $foreignKey . ', GROUP_CONCAT(DISTINCT(s_k.title) ORDER BY s_pk.ordering ASC SEPARATOR ' . $separatorQuoted . ') AS keywords')
      ->from($table . ' AS s_p')
      ->join('inner', $table . '_keyword AS s_pk ON s_pk.' . $foreignKey . ' = s_p.id')
      ->join('inner', '#__keyword AS s_k ON s_k.id = s_pk.id_keyword')
      ->group('s_p.id');

    return $this
      ->select('k.keywords')
      ->join('left', '(' . (string)$sub . ') AS k ON k.' . $foreignKey . ' = a.id');
  }

  // --- FILTER helpers (add WHERE conditions) ---

  public function filterPublished(int $value): static
  {
    if ($value === 1) {
      $this->where('a.published = 1');
    } elseif ($value === 0) {
      $this->where('a.published = 0');
    }
    return $this;
  }

  public function filterCategory(int $value): static
  {
    if ($value === -1) {
      $this->where('a.id_category IS NULL');
    } elseif ($value > 0) {
      $this->where('a.id_category = :id_category')
        ->bindValue(':id_category', $value, 'int');
    }
    return $this;
  }

  public function filterKeywords(int $value, string $table, string $foreignKey): static
  {
    if ($value > 0) {
      $this->join('inner', $table . '_keyword AS pk ON (pk.' . $foreignKey . ' = a.id AND pk.id_keyword = :id_keyword)')
        ->bindValue(':id_keyword', $value, 'int');
    }
    return $this;
  }
}
