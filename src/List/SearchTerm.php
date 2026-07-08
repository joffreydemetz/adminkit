<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\AdminKit\List;

/**
 * Splits a raw search string into an optional CODE and the search TEXT.
 *
 *   "hello"        -> code '',    text 'hello'
 *   "EMAIL:foo"    -> code 'EMAIL', text 'foo'
 *   "A:B:C"        -> code 'A',   text 'B'   (only the first two segments are read)
 *
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class SearchTerm
{
  public readonly string $code;
  public readonly string $text;

  public function __construct(string $search)
  {
    $parts = explode(':', $search);

    if (1 === count($parts)) {
      $this->code = '';
      $this->text = trim($search);
    } else {
      $this->code = $parts[0];
      $this->text = trim($parts[1]);
    }
  }
}
