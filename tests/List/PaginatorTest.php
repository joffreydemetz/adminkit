<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\AdminKit\Tests\List;

use JDZ\AdminKit\List\Paginator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Paginator::class)]
class PaginatorTest extends TestCase
{
    public function testFirstPage(): void
    {
        $p = new Paginator(total: 45, page: 1, limit: 20);

        $this->assertEquals(1, $p->page);
        $this->assertEquals(0, $p->start);
        $this->assertEquals(3, $p->nbPages);
    }

    public function testMiddlePageOffset(): void
    {
        $p = new Paginator(total: 45, page: 2, limit: 20);

        $this->assertEquals(2, $p->page);
        $this->assertEquals(20, $p->start);
        $this->assertEquals(3, $p->nbPages);
    }

    public function testPageBeyondLastIsClampedToLast(): void
    {
        // page 9 requested but only 3 pages exist
        $p = new Paginator(total: 45, page: 9, limit: 20);

        $this->assertEquals(3, $p->page);
        $this->assertEquals(40, $p->start);
        $this->assertEquals(3, $p->nbPages);
    }

    public function testExactMultipleTotal(): void
    {
        $p = new Paginator(total: 40, page: 2, limit: 20);

        $this->assertEquals(2, $p->page);
        $this->assertEquals(20, $p->start);
        $this->assertEquals(2, $p->nbPages);
    }

    public function testZeroLimitYieldsSinglePage(): void
    {
        $p = new Paginator(total: 45, page: 1, limit: 0);

        $this->assertEquals(1, $p->nbPages);
        $this->assertEquals(0, $p->start);
    }

    public function testSinglePage(): void
    {
        $p = new Paginator(total: 5, page: 1, limit: 20);

        $this->assertEquals(1, $p->page);
        $this->assertEquals(0, $p->start);
        $this->assertEquals(1, $p->nbPages);
    }

    public static function edgeCases(): array
    {
        return [
            // nbPages was 0, page 1 was pulled back to 0, the offset was -20 (an SQL error)
            'no result' => [0, 1, 20, [1, 0, 1]],
            'no result, a later page asked' => [0, 3, 20, [1, 0, 1]],
            // page 0 or below gave a negative offset
            'page 0' => [45, 0, 20, [1, 0, 3]],
            'a negative page' => [45, -2, 20, [1, 0, 3]],
        ];
    }

    #[DataProvider('edgeCases')]
    public function testThePageAndOffsetNeverGoBelowTheFirstPage(int $total, int $page, int $limit, array $expected): void
    {
        $p = new Paginator(total: $total, page: $page, limit: $limit);

        $this->assertSame($expected, [$p->page, $p->start, $p->nbPages]);
    }
}
