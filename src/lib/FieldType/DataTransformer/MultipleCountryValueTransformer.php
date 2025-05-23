<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\DataTransformer;

use Ibexa\Core\FieldType\Country\Value;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * DataTransformer for Country\Value to be used with form type handling multiple selections.
 * Needed to display the form field correctly and transform it back to an appropriate value object.
 */
class MultipleCountryValueTransformer implements DataTransformerInterface
{
    /**
     * @var array Array of countries from "ibexa.field_type.country.data"
     */
    protected array $countriesInfo;

    /**
     * @param array $countriesInfo
     */
    public function __construct(array $countriesInfo)
    {
        $this->countriesInfo = $countriesInfo;
    }

    public function transform(mixed $value): ?array
    {
        if (!$value instanceof Value) {
            return null;
        }

        return array_keys($value->countries);
    }

    public function reverseTransform(mixed $value): ?Value
    {
        if (!is_array($value)) {
            return null;
        }

        $transformedValue = [];
        foreach ($value as $alpha2) {
            $transformedValue[$alpha2] = $this->countriesInfo[$alpha2];
        }

        return new Value($transformedValue);
    }
}
