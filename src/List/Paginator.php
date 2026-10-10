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

    // at least one page, and never below it: no result gave 0 pages, page 1 pulled
    // back to 0 and an offset of -limit (as did a page 0 or below asked for)
    $nbPages = $limit > 0 ? max(1, (int)ceil($total / $limit)) : 1;

    if ($page > $nbPages) {
      $page = $nbPages;
    }

    if ($page < 1) {
      $page = 1;
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
