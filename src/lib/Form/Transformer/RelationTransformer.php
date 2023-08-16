<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Transformer;

use Ibexa\ContentForms\Form\Type\RelationType;
use Symfony\Component\Form\DataTransformerInterface;

final class RelationTransformer implements DataTransformerInterface
{
    public function transform($value): array
    {
        $location = (int)$value;

        if ($location === -1) {
            $locationType = RelationType::LOCATION_SELF;
            $location = null;
        } elseif ($location === 0) {
            $locationType = RelationType::LOCATION_DEFAULT;
        } else {
            $locationType = RelationType::LOCATION_BROWSE;
        }

        return [
            'location' => $location,
            'location_type' => $locationType,
        ];
    }

    public function reverseTransform($value): string
    {
        if ($value === null || $value['location_type'] === RelationType::LOCATION_DEFAULT) {
            return '';
        }

        return (string)($value['location_type'] === RelationType::LOCATION_SELF
            ? RelationType::LOCATION_SELF
            : $value['location']);
    }
}
