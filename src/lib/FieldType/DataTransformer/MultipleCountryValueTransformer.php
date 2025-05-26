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
 * DataTransformer for Country\Value to be used with a form type handling multiple selections.
 * Needed to display the form field correctly and transform it back to an appropriate value object.
 *
 * @phpstan-import-type TCountryValueData from \Ibexa\ContentForms\FieldType\DataTransformer\SingleCountryValueTransformer
 *
 * @implements \Symfony\Component\Form\DataTransformerInterface<\Ibexa\Core\FieldType\Country\Value, string[]|null>
 */
class MultipleCountryValueTransformer implements DataTransformerInterface
{
    /**
     * Array of countries from "ibexa.field_type.country.data".
     *
     * @phpstan-var array<string, TCountryValueData>
     */
    protected array $countriesInfo;

    /**
     * @phpstan-param array<string, TCountryValueData> $countriesInfo
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
