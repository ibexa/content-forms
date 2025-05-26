<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\DataTransformer;

use Ibexa\Core\FieldType\BinaryFile\Value;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Data transformer for an `ezbinaryfile` field type.
 *
 * @phpstan-type TBinaryFileData array{file: string|null, remove: bool, downloadCount: int}
 *
 * @implements \Symfony\Component\Form\DataTransformerInterface<\Ibexa\Core\FieldType\BinaryFile\Value, TBinaryFileData>
 */
class BinaryFileValueTransformer extends AbstractBinaryBaseTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): array
    {
        if (null === $value) {
            $value = $this->fieldType->getEmptyValue();
        }

        return array_merge(
            $this->getDefaultProperties(),
            ['downloadCount' => $value->downloadCount]
        );
    }

    /**
     * @phpstan-param TBinaryFileData $value
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function reverseTransform(mixed $value): ?Value
    {
        /** @var \Ibexa\Core\FieldType\BinaryFile\Value */
        return $this->getReverseTransformedValue($value);
    }
}
