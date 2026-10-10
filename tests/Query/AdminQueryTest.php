<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\AdminKit\Tests\Query;

use JDZ\AdminKit\Query\AdminQuery;
use JDZ\Database\ParamType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The exact SQL each helper adds to an admin list query, and its bound values.
 */
#[CoversClass(AdminQuery::class)]
class AdminQueryTest extends TestCase
{
    private static function base(): AdminQuery
    {
        return (new AdminQuery())->select('a.id')->from('#__article AS a');
    }

    private static function sql(string ...$lines): string
    {
        return implode(PHP_EOL, $lines);
    }

    /** [param => [value, type]] */
    private static function bound(AdminQuery $query): array
    {
        return array_map(fn($b) => [$b->value, $b->dataType], $query->getBounded());
    }

    public function testSelectCategoryJoinsTheCategoryTitleWithAFallback(): void
    {
        $query = self::base()->selectCategory("'-'");

        $this->assertSame(self::sql(
            "SELECT a.id, a.id_category, COALESCE(c.title, '-') AS category",
            'FROM #__article AS a',
            'LEFT JOIN #__category AS c ON c.id = a.id_category',
        ), (string) $query);
    }

    public function testSelectVersion(): void
    {
        $this->assertSame(self::sql('SELECT a.id, a.version', 'FROM #__article AS a'), (string) self::base()->selectVersion());
    }

    public function testSelectKeywordsJoinsTheOrderedKeywordListPerRow(): void
    {
        $query = self::base()->selectKeywords('#__article', 'id_article', "', '");

        $this->assertSame(self::sql(
            'SELECT a.id, k.keywords',
            'FROM #__article AS a',
            "LEFT JOIN (SELECT s_p.id AS id_article, GROUP_CONCAT(DISTINCT(s_k.title) ORDER BY s_pk.ordering ASC SEPARATOR ', ') AS keywords",
            'FROM #__article AS s_p',
            'INNER JOIN #__article_keyword AS s_pk ON s_pk.id_article = s_p.id',
            'INNER JOIN #__keyword AS s_k ON s_k.id = s_pk.id_keyword',
            'GROUP BY s_p.id) AS k ON k.id_article = a.id',
        ), (string) $query);
    }

    public static function filters(): array
    {
        $plain = self::sql('SELECT a.id', 'FROM #__article AS a');

        return [
            'published' => [fn(AdminQuery $q) => $q->filterPublished(1), self::sql('SELECT a.id', 'FROM #__article AS a', 'WHERE a.published = 1'), []],
            'unpublished' => [fn(AdminQuery $q) => $q->filterPublished(0), self::sql('SELECT a.id', 'FROM #__article AS a', 'WHERE a.published = 0'), []],
            'published: any other value adds nothing' => [fn(AdminQuery $q) => $q->filterPublished(-1), $plain, []],
            'without a category' => [fn(AdminQuery $q) => $q->filterCategory(-1), self::sql('SELECT a.id', 'FROM #__article AS a', 'WHERE a.id_category IS NULL'), []],
            'a category, bound as an int' => [
                fn(AdminQuery $q) => $q->filterCategory(5),
                self::sql('SELECT a.id', 'FROM #__article AS a', 'WHERE a.id_category = :id_category'),
                [':id_category' => [5, ParamType::INT->value]],
            ],
            'category 0 adds nothing' => [fn(AdminQuery $q) => $q->filterCategory(0), $plain, []],
            'a keyword, joined and bound as an int' => [
                fn(AdminQuery $q) => $q->filterKeywords(7, '#__article', 'id_article'),
                self::sql('SELECT a.id', 'FROM #__article AS a', 'INNER JOIN #__article_keyword AS pk ON (pk.id_article = a.id AND pk.id_keyword = :id_keyword)'),
                [':id_keyword' => [7, ParamType::INT->value]],
            ],
            'keyword 0 adds nothing' => [fn(AdminQuery $q) => $q->filterKeywords(0, '#__article', 'id_article'), $plain, []],
            'filters combine with AND' => [
                fn(AdminQuery $q) => $q->filterPublished(1)->filterCategory(5),
                self::sql('SELECT a.id', 'FROM #__article AS a', 'WHERE a.published = 1 AND a.id_category = :id_category'),
                [':id_category' => [5, ParamType::INT->value]],
            ],
        ];
    }

    #[DataProvider('filters')]
    public function testFilter(\Closure $filter, string $sql, array $bound): void
    {
        $query = $filter(self::base());

        $this->assertSame($sql, (string) $query);
        $this->assertSame($bound, self::bound($query));
    }
}
