<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Form\Transformer;

use Ibexa\ContentForms\Form\Transformer\JsonToArrayTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class JsonToArrayTransformerTest extends TestCase
{
    /**
     * @return iterable<string, array{mixed, string}>
     */
    public static function provideDataForTestTransform(): iterable
    {
        yield 'null value' => [null, ''];
        yield 'associative array' => [['foo' => 'bar'], '{"foo":"bar"}'];
        yield 'empty array' => [[], '{}'];
    }

    /**
     * @param string[]|null $value
     * @dataProvider provideDataForTestTransform
     */
    public function testTransform(?array $value, string $expected): void
    {
        $transformer = new JsonToArrayTransformer();

        self::assertSame($expected, $transformer->transform($value));
    }

    /**
     * @return iterable<string, array{string|null, mixed}>
     */
    public static function provideDataForTestReverseTransform(): iterable
    {
        yield 'null value' => [null, []];
        yield 'empty string' => ['', []];
        yield 'JSON string' => ['{"foo":"bar"}', ['foo' => 'bar']];
        yield 'zero string' => ['0', 0];
    }

    /**
     * @param string[]|int $expected
     * @dataProvider provideDataForTestReverseTransform
     */
    public function testReverseTransform(?string $value, $expected): void
    {
        $transformer = new JsonToArrayTransformer();

        self::assertSame($expected, $transformer->reverseTransform($value));
    }

    public function testReverseTransformInvalidJsonThrowsException(): void
    {
        $transformer = new JsonToArrayTransformer();

        $this->expectException(TransformationFailedException::class);
        $transformer->reverseTransform('{invalid}');
    }
}
