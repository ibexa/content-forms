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

class MultiSelectionValueTransformerTest extends TestCase
{
    public function transformProvider()
    {
        return [
            [[0]],
            [['null']],
            [[1, 2]],
            [['forty', 'two']],
            [[1, 4, 1, 5, 9, 2, 6]],
        ];
    }

    /**
     * @dataProvider transformProvider
     */
    public function testTransform($valueAsArray)
    {
        $transformer = new MultiSelectionValueTransformer();
        $value = new Value($valueAsArray);
        self::assertSame($valueAsArray, $transformer->transform($value));
    }

    /**
     * @dataProvider transformProvider
     */
    public function testReverseTransform($valueAsArray)
    {
        $transformer = new MultiSelectionValueTransformer();
        $expectedValue = new Value($valueAsArray);
        self::assertEquals($expectedValue, $transformer->reverseTransform($valueAsArray));
    }

    public function transformNullProvider()
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
    public function testTransformNull($value)
    {
        $transformer = new MultiSelectionValueTransformer();
        self::assertNull($transformer->transform($value));
    }

    public function testReverseTransformNull()
    {
        $transformer = new MultiSelectionValueTransformer();
        self::assertNull($transformer->reverseTransform(null));
    }
}

class_alias(MultiSelectionValueTransformerTest::class, 'EzSystems\EzPlatformContentForms\Tests\FieldType\DataTransformer\MultiSelectionValueTransformerTest');
