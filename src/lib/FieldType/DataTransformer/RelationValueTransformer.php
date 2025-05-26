<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\DataTransformer;

use Ibexa\Core\FieldType\Relation\Value;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements \Symfony\Component\Form\DataTransformerInterface<\Ibexa\Core\FieldType\Relation\Value, int|null>
 */
class RelationValueTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): ?int
    {
        if (!$value instanceof Value) {
            return null;
        }

        return $value->destinationContentId ?? null;
    }

    public function reverseTransform(mixed $value): ?Value
    {
        if (!is_numeric($value)) {
            return null;
        }

        return new Value($value);
    }
}
