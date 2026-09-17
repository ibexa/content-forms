<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\FieldType\DataTransformer;

use Ibexa\ContentForms\FieldType\DataTransformer\SingleSelectionValueTransformer;
use Ibexa\Core\FieldType\Selection\Value;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SingleSelectionValueTransformerTest extends TestCase
{
    /**
     * @phpstan-return list<array{int}>
     */
    public static function transformProvider(): array
    {
        return [
            [0],
            [1],
            [42],
        ];
    }

    #[DataProvider('transformProvider')]
    public function testTransform(int $value): void
    {
        $transformer = new SingleSelectionValueTransformer();
        self::assertSame($value, $transformer->transform(new Value([$value])));
    }

    #[DataProvider('transformProvider')]
    public function testReverseTransform(int $value): void
    {
        $transformer = new SingleSelectionValueTransformer();
        $expectedValue = new Value([$value]);
        self::assertEquals($expectedValue, $transformer->reverseTransform($value));
    }

    /**
     * @phpstan-return list<array{mixed}>
     */
    public static function transformNullProvider(): array
    {
        return [
            [new Value()],
            [[]],
            [false],
            [''],
        ];
    }

    #[DataProvider('transformNullProvider')]
    public function testTransformNull(mixed $value): void
    {
        $transformer = new SingleSelectionValueTransformer();
        self::assertNull($transformer->transform($value));
    }

    public function testReverseTransformNull(): void
    {
        $transformer = new SingleSelectionValueTransformer();
        self::assertNull($transformer->reverseTransform(null));
    }
}
