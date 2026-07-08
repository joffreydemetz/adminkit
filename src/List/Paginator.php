<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\AdminKit\List;

/**
 * Clamps a requested page against a result total and derives the SQL offset.
 *
 * Preserves the exact clamp behavior of the legacy list orchestration:
 *  - nbPages = ceil(total / limit)   (or 1 when limit is 0)
 *  - a page past the last page is pulled back to the last page
 *  - an offset that lands beyond the total resets to page 1 / offset 0
 *
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Paginator
{
  public readonly int $total;
  public readonly int $limit;
  public readonly int $page;
  public readonly int $start;
  public readonly int $nbPages;

  public function __construct(int $total, int $page = 1, int $limit = 20)
  {
    $this->total = $total;
    $this->limit = $limit;

    $nbPages = $limit > 0 ? (int)ceil($total / $limit) : 1;

    if ($page > $nbPages) {
      $page = $nbPages;
    }

    $start = ($page - 1) * $limit;

    if ($start >= $total) {
      $page = 1;
      $start = 0;
    }

    $this->page = $page;
    $this->start = $start;
    $this->nbPages = $nbPages;
  }
}
