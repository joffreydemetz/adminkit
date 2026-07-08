<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\AdminKit\Tests\List;

use JDZ\AdminKit\List\OrderingValidator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JDZ\AdminKit\List\OrderingValidator
 */
class OrderingValidatorTest extends TestCase
{
    /** all gates open */
    private function valid(array $overrides = []): bool
    {
        $args = array_merge([
            'canOrder' => true,
            'total' => 5,
            'search' => '',
            'defaultOrderBy' => 'a.ordering ASC',
            'orderBy' => 'a.ordering ASC',
            'canPublish' => false,
            'published' => '',
            'validOrderingPublishedState' => '',
        ], $overrides);

        return OrderingValidator::isValid(
            $args['canOrder'],
            $args['total'],
            $args['search'],
            $args['defaultOrderBy'],
            $args['orderBy'],
            $args['canPublish'],
            $args['published'],
            $args['validOrderingPublishedState']
        );
    }

    public function testHappyPath(): void
    {
        $this->assertTrue($this->valid());
    }

    public function testCannotOrder(): void
    {
        $this->assertFalse($this->valid(['canOrder' => false]));
    }

    public function testFewerThanTwoRows(): void
    {
        $this->assertFalse($this->valid(['total' => 1]));
    }

    public function testActiveSearchBlocks(): void
    {
        $this->assertFalse($this->valid(['search' => 'foo']));
    }

    public function testNonDefaultOrderingBlocks(): void
    {
        $this->assertFalse($this->valid(['orderBy' => 'a.title ASC']));
    }

    public function testPublishedFilterMismatchBlocksWhenPublishable(): void
    {
        // canPublish + published filter differs from the reorder-eligible state
        $this->assertFalse($this->valid([
            'canPublish' => true,
            'published' => 'N',
            'validOrderingPublishedState' => '',
        ]));
    }

    public function testPublishedFilterMatchPassesWhenPublishable(): void
    {
        $this->assertTrue($this->valid([
            'canPublish' => true,
            'published' => 'Y',
            'validOrderingPublishedState' => 'Y',
        ]));
    }

    public function testPublishedFilterIgnoredWhenNotPublishable(): void
    {
        // canPublish false -> published mismatch does not matter
        $this->assertTrue($this->valid([
            'canPublish' => false,
            'published' => 'anything',
            'validOrderingPublishedState' => '',
        ]));
    }
}
