<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\DataTransformer;

use Ibexa\Core\FieldType\Selection\Value;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * DataTransformer for Selection\Value in multi select mode.
 */
class MultiSelectionValueTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): ?array
    {
        if (!$value instanceof Value) {
            return null;
        }

        if ($value->selection === []) {
            return null;
        }

        return $value->selection;
    }

    public function reverseTransform(mixed $value): ?Value
    {
        if ($value === null) {
            return null;
        }

        return new Value($value);
    }
}
