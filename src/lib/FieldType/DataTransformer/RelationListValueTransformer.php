<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\DataTransformer;

use Ibexa\Core\FieldType\RelationList\Value;
use Override;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * DataTransformer for RelationList\Value in single select mode.
 */
class RelationListValueTransformer implements DataTransformerInterface
{
    #[Override]
    public function transform($value)
    {
        if (!$value instanceof Value) {
            return null;
        }

        if ($value->destinationContentIds === []) {
            return null;
        }

        return implode(',', $value->destinationContentIds);
    }

    #[Override]
    public function reverseTransform($value)
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
