<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\DataTransformer;

use Ibexa\Core\FieldType\Image\Value;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Data transformer for the `ibexa_image` field type.
 *
 * @phpstan-type TImageData array{file: string|null, remove: boolean, alternativeText: string|null, additionalData: array<string>}
 *
 * @implements \Symfony\Component\Form\DataTransformerInterface<\Ibexa\Core\FieldType\Image\Value, array<string, mixed>>
 */
final class ImageValueTransformer extends AbstractBinaryBaseTransformer implements DataTransformerInterface
{
    /**
     * @param Value $value
     *
     * @return array<string, mixed>
     */
    public function transform(mixed $value): array
    {
        if (null === $value) {
            $value = $this->fieldType->getEmptyValue();
        }

        return array_merge(
            $this->getDefaultProperties(),
            [
                'alternativeText' => $value->alternativeText,
                'additionalData' => $value->additionalData,
            ]
        );
    }

    /**
     * @param array<string, mixed> $value
     *
     * @throws TransformationFailedException
     */
    public function reverseTransform(mixed $value): Value
    {
        /** @var Value $valueObject */
        $valueObject = $this->getReverseTransformedValue($value);

        if ($this->fieldType->isEmptyValue($valueObject)) {
            return $valueObject;
        }

        $valueObject->alternativeText = $value['alternativeText'];
        $valueObject->additionalData = $value['additionalData'];

        return $valueObject;
    }
}
