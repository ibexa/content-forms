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
 * Data transformer for ezbinaryfile field type.
 *
 * {@inheritdoc}
 */
class BinaryFileValueTransformer extends AbstractBinaryBaseTransformer implements DataTransformerInterface
{
    /**
     * @param \Ibexa\Core\FieldType\BinaryFile\Value $value
     *
     * @return array
     */
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
     * @param array $value
     *
     * @return \Ibexa\Core\FieldType\BinaryFile\Value
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function reverseTransform(mixed $value): ?Value
    {
        /** @var \Ibexa\Core\FieldType\BinaryFile\Value $valueObject */
        $valueObject = $this->getReverseTransformedValue($value);

        if ($this->fieldType->isEmptyValue($valueObject)) {
            return $valueObject;
        }

        return $valueObject;
    }
}
