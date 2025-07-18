<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\FieldType\DataTransformer;

use Ibexa\ContentForms\FieldType\DataTransformer\MultiSelectionValueTransformer;
use Ibexa\Core\FieldType\Selection\Value;
use PHPUnit\Framework\TestCase;

final class MultiSelectionValueTransformerTest extends TestCase
{
    /**
     * @phpstan-return list<array{array<int>}>
     */
    public function transformProvider(): array
    {
        return [
            [[0]],
            [[1, 2]],
            [[1, 4, 1, 5, 9, 2, 6]],
        ];
    }

    /**
     * @dataProvider transformProvider
     *
     * @param array<int> $valueAsArray
     */
    public function testTransform(array $valueAsArray): void
    {
        $transformer = new MultiSelectionValueTransformer();
        $value = new Value($valueAsArray);
        self::assertSame($valueAsArray, $transformer->transform($value));
    }

    /**
     * @dataProvider transformProvider
     *
     * @param array<int> $valueAsArray
     */
    public function testReverseTransform(array $valueAsArray): void
    {
        $transformer = new MultiSelectionValueTransformer();
        $expectedValue = new Value($valueAsArray);
        self::assertEquals($expectedValue, $transformer->reverseTransform($valueAsArray));
    }

    /**
     * @phpstan-return list<array{mixed}>
     */
    public function transformNullProvider(): array
    {
        return [
            [new Value()],
            [[]],
            [42],
            [false],
            [[0, 1]],
        ];
    }

    /**
     * @dataProvider transformNullProvider
     */
    public function testTransformNull(mixed $value): void
    {
        $transformer = new MultiSelectionValueTransformer();
        self::assertNull($transformer->transform($value));
    }

    public function testReverseTransformNull(): void
    {
        $transformer = new MultiSelectionValueTransformer();
        self::assertNull($transformer->reverseTransform(null));
    }
}
