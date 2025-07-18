<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\DataTransformer;

use Ibexa\Core\FieldType\Keyword\Value;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * DataTransformer for Keyword\Value.
 *
 * @implements \Symfony\Component\Form\DataTransformerInterface<\Ibexa\Core\FieldType\Keyword\Value, string|null>
 */
final readonly class KeywordValueTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): ?string
    {
        if (!$value instanceof Value) {
            return null;
        }

        return implode(', ', $value->values);
    }

    public function reverseTransform(mixed $value): ?Value
    {
        if (empty($value)) {
            return null;
        }

        return new Value($value);
    }
}
