<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\AdminKit\Tests\Item;

use JDZ\AdminKit\Item\DataSanitizer;
use JDZ\Form\FormData;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JDZ\AdminKit\Item\DataSanitizer
 */
class DataSanitizerTest extends TestCase
{
    private const NULL_DATETIME = '1000-01-01 00:00:00';
    private const NULL_DATE = '1000-01-01';

    private function field(string $type, string $null): object
    {
        return (object)['Type' => $type, 'Null' => $null];
    }

    private function sanitize(array $fields, array $data): FormData
    {
        $form = new FormData($data);
        (new DataSanitizer($fields, self::NULL_DATETIME, self::NULL_DATE))->sanitize($form);
        return $form;
    }

    public function testNullableEmptyDatetimeBecomesNull(): void
    {
        $form = $this->sanitize(
            ['publishDate' => $this->field('datetime', 'YES')],
            ['publishDate' => '']
        );

        $this->assertNull($form->get('publishDate'));
    }

    public function testNullableNullDateStringBecomesNull(): void
    {
        $form = $this->sanitize(
            ['removeDate' => $this->field('date', 'YES')],
            ['removeDate' => self::NULL_DATE]
        );

        $this->assertNull($form->get('removeDate'));
    }

    public function testNullableEmptyIntBecomesNull(): void
    {
        $form = $this->sanitize(
            ['id_category' => $this->field('smallint', 'YES')],
            ['id_category' => '']
        );

        $this->assertNull($form->get('id_category'));
    }

    public function testNotNullEmptyIntCastToInt(): void
    {
        $form = $this->sanitize(
            ['ordering' => $this->field('smallint', 'NO')],
            ['ordering' => '']
        );

        $this->assertSame(0, $form->get('ordering'));
    }

    public function testNotNullEmptyDecimalCastToFloat(): void
    {
        $form = $this->sanitize(
            ['price' => $this->field('decimal', 'NO')],
            ['price' => '']
        );

        $this->assertSame(0.0, $form->get('price'));
    }

    public function testNotNullEmptyDatetimeGetsNullDate(): void
    {
        $form = $this->sanitize(
            ['createdDate' => $this->field('datetime', 'NO')],
            ['createdDate' => '']
        );

        $this->assertSame(self::NULL_DATETIME, $form->get('createdDate'));
    }

    public function testNonEmptyValuesUntouched(): void
    {
        $form = $this->sanitize(
            [
                'title' => $this->field('varchar', 'NO'),
                'ordering' => $this->field('smallint', 'NO'),
                'publishDate' => $this->field('datetime', 'YES'),
            ],
            [
                'title' => 'Kept',
                'ordering' => '5',
                'publishDate' => '2026-07-09 10:00:00',
            ]
        );

        $this->assertEquals('Kept', $form->get('title'));
        $this->assertEquals('5', $form->get('ordering'));
        $this->assertEquals('2026-07-09 10:00:00', $form->get('publishDate'));
    }

    public function testFieldsAbsentFromDataAreSkipped(): void
    {
        $form = $this->sanitize(
            ['ghost' => $this->field('smallint', 'NO')],
            ['title' => 'Only me']
        );

        $this->assertFalse($form->has('ghost'));
        $this->assertEquals('Only me', $form->get('title'));
    }
}
