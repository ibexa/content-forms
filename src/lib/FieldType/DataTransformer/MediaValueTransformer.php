<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\DataTransformer;

use Ibexa\Core\FieldType\Media\Value;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Data transformer for ibexa_media field type.
 *
 * @phpstan-type TMediaData array{file: string|null, remove: bool, hasController: bool, loop: bool, autoplay: bool, width: int, height: int}
 *
 * @implements \Symfony\Component\Form\DataTransformerInterface<\Ibexa\Core\FieldType\Media\Value, TMediaData>
 */
final class MediaValueTransformer extends AbstractBinaryBaseTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): array
    {
        if (null === $value) {
            $value = $this->fieldType->getEmptyValue();
        }

        return array_merge(
            $this->getDefaultProperties(),
            [
                'hasController' => $value->hasController,
                'loop' => $value->loop,
                'autoplay' => $value->autoplay,
                'width' => $value->width,
                'height' => $value->height,
            ]
        );
    }

    /**
     * @phpstan-param TMediaData $value
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function reverseTransform(mixed $value): Value
    {
        /** @var \Ibexa\Core\FieldType\Media\Value $valueObject */
        $valueObject = $this->getReverseTransformedValue($value);

        if ($this->fieldType->isEmptyValue($valueObject)) {
            return $valueObject;
        }

        $valueObject->hasController = $value['hasController'];
        $valueObject->loop = $value['loop'];
        $valueObject->autoplay = $value['autoplay'];
        $valueObject->width = $value['width'];
        $valueObject->height = $value['height'];

        return $valueObject;
    }
}
