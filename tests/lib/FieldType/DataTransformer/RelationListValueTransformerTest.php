<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\FieldType\DataTransformer;

use Ibexa\ContentForms\FieldType\DataTransformer\RelationListValueTransformer;
use Ibexa\Core\FieldType\RelationList\Value;
use PHPUnit\Framework\TestCase;

final class RelationListValueTransformerTest extends TestCase
{
    /**
     * @dataProvider dataProviderForTestReverseTransform
     */
    public function testReverseTransform(?string $value, ?Value $expectedValue): void
    {
        $transformer = new RelationListValueTransformer();

        self::assertEquals(
            $expectedValue,
            $transformer->reverseTransform($value)
        );
    }

    public function dataProviderForTestReverseTransform(): iterable
    {
        yield 'null' => [
            null,
            null,
        ];

        yield 'optimistic' => [
            '1,2,3,5,8,13',
            new Value([1, 2, 3, 5, 8, 13]),
        ];
    }
}
