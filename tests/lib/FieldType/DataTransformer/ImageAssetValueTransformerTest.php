<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\FieldType\DataTransformer;

use Ibexa\ContentForms\FieldType\DataTransformer\ImageAssetValueTransformer;
use Ibexa\Contracts\Core\Repository\FieldType;
use Ibexa\Core\FieldType\ImageAsset\Value;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\ContentForms\FieldType\DataTransformer\ImageAssetValueTransformer
 */
final class ImageAssetValueTransformerTest extends TestCase
{
    /**
     * @dataProvider provideValuesForTransform
     *
     * @param array{file: string|null, remove: bool, destinationContentId: int|null, alternativeText: string|null} $expectedFormData
     */
    public function testTransform(
        Value $value,
        array $expectedFormData
    ): void {
        $transformer = new ImageAssetValueTransformer(
            self::createStub(FieldType::class),
            new Value(),
            Value::class
        );

        self::assertSame($expectedFormData, $transformer->transform($value));
    }

    /**
     * @return iterable<string, array{Value, array{file: string|null, remove: bool, destinationContentId: int|null, alternativeText: string|null}}>
     */
    public static function provideValuesForTransform(): iterable
    {
        yield 'empty value keeps destinationContentId empty' => [
            new Value(),
            [
                'file' => null,
                'remove' => false,
                'destinationContentId' => null,
                'alternativeText' => null,
            ],
        ];

        yield 'value with an asset' => [
            new Value('42', 'Alternative text'),
            [
                'file' => null,
                'remove' => false,
                'destinationContentId' => 42,
                'alternativeText' => 'Alternative text',
            ],
        ];
    }
}
