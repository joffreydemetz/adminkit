<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\AdminKit\List;

use JDZ\Form\Contract\FormInterface;
use JDZ\Form\Field\Search;
use JDZ\Form\Field\Select;
use JDZ\Form\Filter\IntFilter;
use JDZ\Form\FormButton;
use JDZ\Form\FormRow;
use JDZ\Form\FormRow\Hidden as HiddenRow;
use JDZ\Form\Field\Hidden as HiddenField;
use JDZ\Form\FormRow\Inputgroup;
use JDZ\Form\SelectFieldOption;

/**
 * Builds the standard admin filterbar structure on a form: hidden
 * component/listUrl carriers, a `searchbox` fieldset (code select + search
 * input + submit/reset buttons), a `sorting` fieldset (limit + orderBy
 * selects) and an empty `filters` fieldset for per-component filters.
 *
 * Labels go through an injected translator (`fn(string $key): string`), so
 * the scaffold carries no i18n, HTTP or framework coupling. The consumer
 * keeps the returned orderBy/code selects to append component-specific
 * options.
 *
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class FilterFormScaffold
{
  private Select $orderByField;
  private Select $codeField;

  /** @param \Closure(string):string $translate */
  public function __construct(
    private FormInterface $form,
    private string $prefix,
    private \Closure $translate
  ) {}

  /**
   * Build the filterbar structure. Mirrors the legacy admin filter form
   * layout exactly (row order matters for rendered output).
   */
  public function build(): static
  {
    $this->form->addFormRow(
      (new HiddenRow('component'))
        ->setField(new HiddenField('component'))
    );

    $this->form->addFormRow(
      (new HiddenRow('listUrl'))
        ->setField(new HiddenField('listUrl'))
    );

    $searchboxFieldset = $this->form->makeFormFieldset('searchbox');
    $sortingFieldset = $this->form->makeFormFieldset('sorting');
    $this->form->makeFormFieldset('filters');

    $this->orderByField = (new Select('orderBy'))
      ->setOptions([new SelectFieldOption('', ' - ')]);

    $this->codeField = (new Select('code'))
      ->setOptions([new SelectFieldOption('', ' - ')]);

    $sortingFieldset->addFormRow(
      (new FormRow('limit'))
        ->withLabel(false)
        ->setPrefix($this->prefix)
        ->setField(
          (new Select('limit'))
            ->addFilter(new IntFilter())
            ->setOptions([
              new SelectFieldOption('10', '10'),
              new SelectFieldOption('20', '20'),
              new SelectFieldOption('50', '50'),
              new SelectFieldOption('100', '100'),
            ])
        )
    );

    $sortingFieldset->addFormRow(
      (new FormRow('orderBy'))
        ->withLabel(false)
        ->setPrefix($this->prefix)
        ->setField($this->orderByField)
    );

    $searchInput = (new Inputgroup('search'))
      ->withLabel(false)
      ->setPrefix($this->prefix)
      ->addPart($this->codeField, 'code')
      ->addPart(new Search('search'), 'search')
      ->addPart(
        (new FormButton('submit'))
          ->setTag('button')
          ->setIcon('halflings halflings-search')
          ->addStyle('btn-success')
          ->addDataAttr('submit', 'true'),
        'ok'
      )
      ->addPart(
        (new FormButton('reset'))
          ->setTag('button')
          ->setIcon('halflings halflings-remove')
          ->addStyle('btn-danger')
          ->addDataAttr('reset', 'true'),
        'reset'
      );

    $searchboxFieldset->addFormRow($searchInput);

    $this->orderByField
      ->addOption(new SelectFieldOption('a.id ASC', 'ID ' . ($this->translate)('ORDER_ASC')))
      ->addOption(new SelectFieldOption('a.id DESC', 'ID ' . ($this->translate)('ORDER_DESC')));

    $this->codeField
      ->addOption(new SelectFieldOption('ID', ($this->translate)('ID')));

    return $this;
  }

  public function getOrderByField(): Select
  {
    return $this->orderByField;
  }

  public function getCodeField(): Select
  {
    return $this->codeField;
  }

  /**
   * Title ASC/DESC ordering options + TITLE search code.
   */
  public function addTitleSortOptions(): static
  {
    $this->orderByField
      ->addOption(new SelectFieldOption('a.title ASC', ($this->translate)('TITLE') . ' ' . ($this->translate)('ORDER_ASC')))
      ->addOption(new SelectFieldOption('a.title DESC', ($this->translate)('TITLE') . ' ' . ($this->translate)('ORDER_DESC')));

    $this->codeField
      ->addOption(new SelectFieldOption('TITLE', ($this->translate)('TITLE')));

    return $this;
  }

  /**
   * Yes/no published select in the `filters` fieldset.
   */
  public function addPublishedFilter(): static
  {
    $this->form->getFieldset('filters')->addFormRow(
      (new FormRow('published'))
        ->withLabel(false)
        ->setPrefix($this->prefix)
        ->setField(
          (new Select('published'))
            ->setOptions([
              new SelectFieldOption('', ($this->translate)('PUBLISHED')),
              new SelectFieldOption('Y', ($this->translate)('YES')),
              new SelectFieldOption('N', ($this->translate)('NO')),
            ])
        )
    );

    return $this;
  }
}
