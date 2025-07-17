<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\DataTransformer;

use Ibexa\Core\FieldType\Time\Value;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * DataTransformer for Time\Value.
 *
 * @implements \Symfony\Component\Form\DataTransformerInterface<\Ibexa\Core\FieldType\Time\Value, int|null>
 */
final readonly class TimeValueTransformer implements DataTransformerInterface
{
    /**
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function transform(mixed $value): ?int
    {
        if (!$value instanceof Value) {
            throw new TransformationFailedException(
                sprintf('Expected a %s', Value::class)
            );
        }

        return $value->time ?? null;
    }

    /**
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function reverseTransform(mixed $value): ?Value
    {
        if (null === $value || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            throw new TransformationFailedException(
                sprintf('Received %s instead of a numeric value', gettype($value))
            );
        }

        return new Value($value);
    }
}
