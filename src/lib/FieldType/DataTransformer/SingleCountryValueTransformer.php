<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\DataTransformer;

use Ibexa\Core\FieldType\Country\Value;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * DataTransformer for Country\Value to be used with form type handling only single selection.
 * Needed to display the form field correctly and transform it back to an appropriate value object.
 */
class SingleCountryValueTransformer implements DataTransformerInterface
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

    public function transform(mixed $value): ?string
    {
        if (!$value instanceof Value) {
            return null;
        }

        if (empty($value->countries)) {
            return null;
        }

        if (empty($value->countries)) {
            return null;
        }

        $country = current($value->countries);
        if (!isset($country['Alpha2'])) {
            throw new TransformationFailedException('Missing Alpha2 key');
        }

        return $country['Alpha2'];
    }

    public function reverseTransform(mixed $value): ?Value
    {
        if (empty($value)) {
            return null;
        }

        return new Value([$value => $this->countriesInfo[$value]]);
    }
}
