<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\AdminKit\Tests\List;

use JDZ\AdminKit\List\FilterStateResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilterStateResolver::class)]
class FilterStateResolverTest extends TestCase
{
    public function testExtractFilterParamsStripsPrefix(): void
    {
        $params = FilterStateResolver::extractFilterParams([
            'f_published' => 'Y',
            'f_id_category' => '3',
            'page' => '2',
            'other' => 'x',
        ]);

        $this->assertEquals(['published' => 'Y', 'id_category' => '3'], $params);
    }

    public function testResolvePrecedenceStoredOverridesDefaults(): void
    {
        $data = FilterStateResolver::resolve(
            defaults: ['limit' => 20, 'search' => ''],
            stored: ['limit' => 50],
            queryParams: [],
            force: [],
            token: 'tok',
            queryPage: null,
            defaultOrderBy: 'a.id ASC'
        );

        $this->assertEquals(50, $data['limit']);
    }

    public function testResolvePrecedenceQueryOverridesStored(): void
    {
        $data = FilterStateResolver::resolve(
            defaults: ['published' => ''],
            stored: ['published' => 'Y'],
            queryParams: ['published' => 'N'],
            force: [],
            token: 'tok',
            queryPage: null,
            defaultOrderBy: 'a.id ASC'
        );

        $this->assertEquals('N', $data['published']);
    }

    public function testResolvePrecedenceForceWins(): void
    {
        $data = FilterStateResolver::resolve(
            defaults: ['published' => ''],
            stored: ['published' => 'Y'],
            queryParams: ['published' => 'N'],
            force: ['published' => 'FORCED'],
            token: 'tok',
            queryPage: null,
            defaultOrderBy: 'a.id ASC'
        );

        $this->assertEquals('FORCED', $data['published']);
    }

    public function testTokenIsInjected(): void
    {
        $data = FilterStateResolver::resolve([], [], [], [], 'the-token', null, 'a.id ASC');

        $this->assertEquals('the-token', $data['t']);
    }

    public function testPageFallsBackToStoredThenOne(): void
    {
        $absent = FilterStateResolver::resolve([], [], [], [], 't', null, 'a.id ASC');
        $this->assertEquals(1, $absent['page']);

        $stored = FilterStateResolver::resolve([], ['page' => 4], [], [], 't', null, 'a.id ASC');
        $this->assertEquals(4, $stored['page']);
    }

    public function testQueryPageWins(): void
    {
        $data = FilterStateResolver::resolve([], ['page' => 4], [], [], 't', '7', 'a.id ASC');

        $this->assertEquals('7', $data['page']);
    }

    public function testDefaultOrderByAppliedWhenEmpty(): void
    {
        $data = FilterStateResolver::resolve(['orderBy' => ''], [], [], [], 't', null, 'a.title ASC');

        $this->assertEquals('a.title ASC', $data['orderBy']);
    }

    public function testExplicitOrderByKept(): void
    {
        $data = FilterStateResolver::resolve(
            defaults: ['orderBy' => ''],
            stored: ['orderBy' => 'a.date DESC'],
            queryParams: [],
            force: [],
            token: 't',
            queryPage: null,
            defaultOrderBy: 'a.title ASC'
        );

        $this->assertEquals('a.date DESC', $data['orderBy']);
    }
}
