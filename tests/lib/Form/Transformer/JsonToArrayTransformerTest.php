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

class JsonToArrayTransformerTest extends TestCase
{
    public function testTransformNull(): void
    {
        $transformer = new JsonToArrayTransformer();

        self::assertSame('', $transformer->transform(null));
    }

    public function testTransformArray(): void
    {
        $transformer = new JsonToArrayTransformer();

        self::assertSame('{"foo":"bar"}', $transformer->transform(['foo' => 'bar']));
    }

    public function testTransformEmptyArray(): void
    {
        $transformer = new JsonToArrayTransformer();

        self::assertSame('{}', $transformer->transform([]));
    }

    public function testReverseTransformNull(): void
    {
        $transformer = new JsonToArrayTransformer();

        self::assertSame([], $transformer->reverseTransform(null));
    }

    public function testReverseTransformEmptyString(): void
    {
        $transformer = new JsonToArrayTransformer();

        self::assertSame([], $transformer->reverseTransform(''));
    }

    public function testReverseTransformJsonString(): void
    {
        $transformer = new JsonToArrayTransformer();

        self::assertSame(['foo' => 'bar'], $transformer->reverseTransform('{"foo":"bar"}'));
    }

    public function testReverseTransformZeroString(): void
    {
        $transformer = new JsonToArrayTransformer();

        self::assertSame(0, $transformer->reverseTransform('0'));
    }

    public function testReverseTransformInvalidJsonThrowsException(): void
    {
        $transformer = new JsonToArrayTransformer();

        $this->expectException(TransformationFailedException::class);
        $transformer->reverseTransform('{invalid}');
    }
}
