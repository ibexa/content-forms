<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\DataTransformer;

use Ibexa\Core\FieldType\RelationList\Value;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * DataTransformer for RelationList\Value in single select mode.
 *
 * @implements \Symfony\Component\Form\DataTransformerInterface<\Ibexa\Core\FieldType\RelationList\Value, string|null>
 */
final readonly class RelationListValueTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): ?string
    {
        if (!$value instanceof Value) {
            return null;
        }

        if ($value->destinationContentIds === []) {
            return null;
        }

        return implode(',', $value->destinationContentIds);
    }

    public function reverseTransform(mixed $value): ?Value
    {
        if ($value === null) {
            return null;
        }

        $destinationContentIds = explode(',', $value);
        $destinationContentIds = array_map('trim', $destinationContentIds);
        $destinationContentIds = array_map('intval', $destinationContentIds);

        return new Value($destinationContentIds);
    }
}
