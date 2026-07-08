<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\AdminKit\Item;

use JDZ\Database\Contract\DatabaseInterface;
use JDZ\Database\Query\SelectQuery;

/**
 * Checks whether a column value is already taken in a table, optionally
 * excluding the row currently being edited.
 *
 * The column name is supplied by the model (title / slug / ...), never by user
 * input, so its interpolation into the WHERE clause carries no injection risk;
 * the compared value is bound.
 *
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class UniquenessChecker
{
  public function __construct(
    private DatabaseInterface $dbo,
    private string $table,
    private string $keyColumn = 'id'
  ) {}

  /**
   * True when another row already holds this value in the given column.
   */
  public function isTaken(string $column, string $value, int $excludeId = 0): bool
  {
    $query = (new SelectQuery())
      ->select($this->keyColumn)
      ->from($this->table)
      ->where($column . ' = :value')
      ->bindValue(':value', $value);

    if ($excludeId > 0) {
      $query->where($this->keyColumn . ' <> :excludeId')
        ->bindValue(':excludeId', $excludeId, 'int');
    }

    $this->dbo->setQuery($query);

    return (int)$this->dbo->loadResult() > 0;
  }
}
