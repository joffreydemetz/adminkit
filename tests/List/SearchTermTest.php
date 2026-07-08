<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\AdminKit\Tests\List;

use JDZ\AdminKit\List\SearchTerm;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JDZ\AdminKit\List\SearchTerm
 */
class SearchTermTest extends TestCase
{
    public function testPlainSearchHasNoCode(): void
    {
        $t = new SearchTerm('  hello world  ');

        $this->assertEquals('', $t->code);
        $this->assertEquals('hello world', $t->text);
    }

    public function testCodePrefix(): void
    {
        $t = new SearchTerm('EMAIL: foo@bar.com ');

        $this->assertEquals('EMAIL', $t->code);
        $this->assertEquals('foo@bar.com', $t->text);
    }

    public function testOnlyFirstTwoSegmentsAreRead(): void
    {
        $t = new SearchTerm('A:B:C');

        $this->assertEquals('A', $t->code);
        $this->assertEquals('B', $t->text);
    }

    public function testEmptyString(): void
    {
        $t = new SearchTerm('');

        $this->assertEquals('', $t->code);
        $this->assertEquals('', $t->text);
    }

    public function testTrailingColonYieldsEmptyText(): void
    {
        $t = new SearchTerm('ID:');

        $this->assertEquals('ID', $t->code);
        $this->assertEquals('', $t->text);
    }
}
