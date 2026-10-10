<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\AdminKit\Tests\List;

use JDZ\AdminKit\List\FilterFormScaffold;
use JDZ\Form\Form;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilterFormScaffold::class)]
class FilterFormScaffoldTest extends TestCase
{
    private function scaffold(Form $form): FilterFormScaffold
    {
        // translator marks keys so tests can assert pass-through
        return new FilterFormScaffold($form, 'fiForm', fn(string $key): string => '[' . $key . ']');
    }

    public function testBuildCreatesTheThreeFieldsets(): void
    {
        $form = new Form('filters');
        $this->scaffold($form)->build();

        $this->assertTrue($form->hasFieldset('searchbox'));
        $this->assertTrue($form->hasFieldset('sorting'));
        $this->assertTrue($form->hasFieldset('filters'));
        // per-component filters fieldset starts empty
        $this->assertCount(0, $form->getFieldset('filters')->getFormRows());
    }

    public function testBuildAddsHiddenCarriers(): void
    {
        $form = new Form('filters');
        $this->scaffold($form)->build();

        $this->assertTrue($form->hasFormRow('component'));
        $this->assertTrue($form->hasFormRow('listUrl'));
    }

    public function testSortingFieldsetHasLimitAndOrderBy(): void
    {
        $form = new Form('filters');
        $this->scaffold($form)->build();

        $rows = $form->getFieldset('sorting')->getFormRows();
        $this->assertEquals(['limit', 'orderBy'], array_keys($rows));
    }

    public function testOrderByBaseOptions(): void
    {
        $form = new Form('filters');
        $scaffold = $this->scaffold($form)->build();

        $values = array_map(fn($o) => $o->value, $scaffold->getOrderByField()->options);
        $this->assertEquals(['', 'a.id ASC', 'a.id DESC'], $values);

        // labels went through the injected translator
        $labels = array_map(fn($o) => $o->text, $scaffold->getOrderByField()->options);
        $this->assertEquals(' - ', $labels[0]);
        $this->assertEquals('ID [ORDER_ASC]', $labels[1]);
        $this->assertEquals('ID [ORDER_DESC]', $labels[2]);
    }

    public function testSearchboxInputgroupParts(): void
    {
        $form = new Form('filters');
        $this->scaffold($form)->build();

        $search = $form->getFieldset('searchbox')->getFormRow('search');
        $this->assertNotNull($search);
        $this->assertNotNull($search->getPart('code'));
        $this->assertNotNull($search->getPart('search'));
        $this->assertNotNull($search->getPart('ok'));
        $this->assertNotNull($search->getPart('reset'));
    }

    public function testAddTitleSortOptionsAppends(): void
    {
        $form = new Form('filters');
        $scaffold = $this->scaffold($form)->build()->addTitleSortOptions();

        $values = array_map(fn($o) => $o->value, $scaffold->getOrderByField()->options);
        $this->assertEquals(['', 'a.id ASC', 'a.id DESC', 'a.title ASC', 'a.title DESC'], $values);

        $codes = array_map(fn($o) => $o->value, $scaffold->getCodeField()->options);
        $this->assertEquals(['', 'ID', 'TITLE'], $codes);
    }

    public function testAddPublishedFilter(): void
    {
        $form = new Form('filters');
        $this->scaffold($form)->build()->addPublishedFilter();

        $rows = $form->getFieldset('filters')->getFormRows();
        $this->assertEquals(['published'], array_keys($rows));

        $options = $rows['published']->getField()->options;
        $this->assertEquals(['', 'Y', 'N'], array_map(fn($o) => $o->value, $options));
        $this->assertEquals(['[PUBLISHED]', '[YES]', '[NO]'], array_map(fn($o) => $o->text, $options));
    }
}
