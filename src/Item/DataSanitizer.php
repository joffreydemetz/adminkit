<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\AdminKit\Item;

use JDZ\Form\FormData;

/**
 * Coerces posted form values to match their column definitions before save:
 * empties become NULL for nullable columns (or the driver null-date for
 * not-null date columns), and empty numerics are cast to int/float.
 *
 * Column metadata is the `getTableColumns()` shape — objects exposing `Null`
 * ('YES'/'NO') and `Type` (base column type). The two null-date strings come
 * from the database driver, injected so this class stays connection-free.
 *
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class DataSanitizer
{
  /**
   * @param array<string,object> $fields        column name => metadata object (->Null, ->Type)
   * @param string               $nullDatetime  driver null datetime, e.g. '1000-01-01 00:00:00'
   * @param string               $nullDate      driver null date, e.g. '1000-01-01'
   */
  public function __construct(
    private array $fields,
    private string $nullDatetime,
    private string $nullDate
  ) {}

  public function sanitize(FormData $data): void
  {
    foreach ($this->fields as $fieldName => $fieldInfos) {
      if (false === $data->has($fieldName)) {
        continue;
      }

      $value = $data->get($fieldName);

      if ('YES' === $fieldInfos->Null) {
        switch ($fieldInfos->Type) {
          case 'datetime':
          case 'timestamp':
            if (!$value || $this->nullDatetime === $value) {
              $data->set($fieldName, null);
            }
            break;

          case 'date':
            if (!$value || $this->nullDate === $value) {
              $data->set($fieldName, null);
            }
            break;

          case 'time':
            if (!$value || '00:00:00' === $value) {
              $data->set($fieldName, null);
            }
            break;

          case 'bigint':
          case 'mediumint':
          case 'smallint':
          case 'tinyint':
          case 'decimal':
          default:
            if (!$value) {
              $data->set($fieldName, null);
            }
            break;
        }
      } else {
        switch ($fieldInfos->Type) {
          case 'bigint':
          case 'mediumint':
          case 'smallint':
          case 'tinyint':
            if (!$value) {
              $data->set($fieldName, (int)$value);
            }
            break;

          case 'decimal':
            if (!$value) {
              $data->set($fieldName, (float)$value);
            }
            break;

          case 'datetime':
            if (!$value || $this->nullDatetime === $value) {
              $data->set($fieldName, $this->nullDatetime);
            }
            break;

          case 'date':
            if (!$value || $this->nullDate === $value) {
              $data->set($fieldName, $this->nullDate);
            }
            break;
        }
      }
    }
  }
}
