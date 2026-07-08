<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\AdminKit\List;

/**
 * Decides whether drag-and-drop reordering may be offered for the current list.
 *
 * Reordering is only valid when every gate passes:
 *  - the component supports ordering
 *  - there are at least two rows to reorder
 *  - no active search
 *  - the list is on its default ordering
 *  - (when the component publishes) the published filter matches the
 *    reorder-eligible published state
 *
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class OrderingValidator
{
  public static function isValid(
    bool $canOrder,
    int $total,
    mixed $search,
    string $defaultOrderBy,
    mixed $orderBy,
    bool $canPublish,
    mixed $published,
    string $validOrderingPublishedState
  ): bool {
    if (!$canOrder) {
      return false;
    }

    if ($total < 2) {
      return false;
    }

    if ($search) {
      return false;
    }

    if ($defaultOrderBy !== $orderBy) {
      return false;
    }

    if ($canPublish && $published !== $validOrderingPublishedState) {
      return false;
    }

    return true;
  }
}
