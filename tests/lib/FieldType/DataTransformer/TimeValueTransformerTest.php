<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\FieldType\DataTransformer;

use DateTime;
use Ibexa\ContentForms\FieldType\DataTransformer\TimeValueTransformer;
use Ibexa\Core\FieldType\Time\Value;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class TimeValueTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $date = new DateTime();
        $value = Value::fromDateTime($date);
        $transformer = new TimeValueTransformer();
        $time = $date->getTimestamp() - $date->setTime(0, 0, 0)->getTimestamp();

        self::assertSame($time, $transformer->transform($value));
    }

    public function testTransformZero(): void
    {
        $value = new Value(0);
        $transformer = new TimeValueTransformer();

        self::assertSame(0, $transformer->transform($value));
    }

    public function testTransformNull(): void
    {
        $value = new Value(null);
        $transformer = new TimeValueTransformer();

        self::assertNull($transformer->transform($value));
    }

    public function testTransformInvalidValue(): void
    {
        $transformer = new TimeValueTransformer();

        $this->expectException(TransformationFailedException::class);
        /** @phpstan-ignore argument.type */
        $transformer->transform((object) ['time' => 1]);
    }
}
