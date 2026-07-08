<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\AdminKit\Tests\Item;

use JDZ\AdminKit\Item\UniquenessChecker;
use JDZ\Database\Contract\DatabaseInterface;
use JDZ\Database\Query\QueryInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JDZ\AdminKit\Item\UniquenessChecker
 */
class UniquenessCheckerTest extends TestCase
{
    private function dbo(mixed $loadResult): DatabaseInterface
    {
        $dbo = $this->createStub(DatabaseInterface::class);
        $dbo->method('setQuery')->willReturnCallback(fn($q) => $q instanceof QueryInterface ? $q : $q);
        $dbo->method('loadResult')->willReturn($loadResult);
        return $dbo;
    }

    public function testTakenWhenRowFound(): void
    {
        $checker = new UniquenessChecker($this->dbo(7), '#__formation');

        $this->assertTrue($checker->isTaken('title', 'Existing'));
    }

    public function testNotTakenWhenNoRow(): void
    {
        $checker = new UniquenessChecker($this->dbo(null), '#__formation');

        $this->assertFalse($checker->isTaken('title', 'Fresh'));
    }

    public function testNotTakenWhenResultIsZero(): void
    {
        $checker = new UniquenessChecker($this->dbo('0'), '#__formation');

        $this->assertFalse($checker->isTaken('slug', 'fresh'));
    }

    public function testQueryReceivesBoundValueAndExclusion(): void
    {
        $dbo = $this->createMock(DatabaseInterface::class);
        $captured = null;
        $dbo->method('setQuery')->willReturnCallback(function ($q) use (&$captured) {
            $captured = (string)$q;
            return $q;
        });
        $dbo->method('loadResult')->willReturn(null);

        (new UniquenessChecker($dbo, '#__partner'))->isTaken('title', 'X', 12);

        $this->assertStringContainsString('#__partner', $captured);
        $this->assertStringContainsString('title = :value', $captured);
        $this->assertStringContainsString('id <> :excludeId', $captured);
    }

    public function testNoExclusionWhenIdZero(): void
    {
        $dbo = $this->createMock(DatabaseInterface::class);
        $captured = null;
        $dbo->method('setQuery')->willReturnCallback(function ($q) use (&$captured) {
            $captured = (string)$q;
            return $q;
        });
        $dbo->method('loadResult')->willReturn(null);

        (new UniquenessChecker($dbo, '#__partner'))->isTaken('title', 'X', 0);

        $this->assertStringNotContainsString('excludeId', $captured);
    }
}
