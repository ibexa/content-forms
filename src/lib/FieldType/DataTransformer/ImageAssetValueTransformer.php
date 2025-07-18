<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\DataTransformer;

use Ibexa\Core\FieldType\ImageAsset\Value;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @phpstan-type TImageAssetData array{file: string|null, remove: boolean, destinationContentId: int, alternativeText: string|null}
 *
 * @implements \Symfony\Component\Form\DataTransformerInterface<\Ibexa\Core\FieldType\ImageAsset\Value, TImageAssetData|null>
 */
class ImageAssetValueTransformer extends AbstractBinaryBaseTransformer implements DataTransformerInterface
{
    /**
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function transform($value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof Value) {
            throw new TransformationFailedException(
                sprintf('Received %s instead of %s', gettype($value), Value::class)
            );
        }

        return array_merge(
            $this->getDefaultProperties(),
            [
                'destinationContentId' => (int)$value->destinationContentId,
                'alternativeText' => $value->alternativeText,
            ]
        );
    }

    /**
     * @param array|null $value
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     *
     * @return \Ibexa\Core\FieldType\ImageAsset\Value|null
     */
    public function reverseTransform($value): ?Value
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw new TransformationFailedException(
                sprintf('Received %s instead of an array', gettype($value))
            );
        }

        return new Value($value['destinationContentId'], $value['alternativeText']);
    }
}
