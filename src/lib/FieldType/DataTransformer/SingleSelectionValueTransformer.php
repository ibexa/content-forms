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
 * DataTransformer for Selection\Value in single select mode.
 *
 * @implements \Symfony\Component\Form\DataTransformerInterface<\Ibexa\Core\FieldType\Selection\Value, int|null>
 */
class SingleSelectionValueTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): ?int
    {
        if (!$value instanceof Value) {
            return null;
        }

        if (empty($value->selection)) {
            return null;
        }

        return $value->selection[0];
    }

    public function reverseTransform(mixed $value): ?Value
    {
        if ($value === null) {
            return null;
        }

        return new Value([(int)$value]);
    }
}
