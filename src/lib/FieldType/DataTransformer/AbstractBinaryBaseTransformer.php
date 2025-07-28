<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\DataTransformer;

use Ibexa\Contracts\Core\Repository\FieldType;
use Ibexa\Core\FieldType\Value;

/**
 * Base transformer for binary file-based field types.
 */
abstract class AbstractBinaryBaseTransformer
{
    /**
     * @phpstan-param class-string $valueClass
     */
    public function __construct(
        protected readonly FieldType $fieldType,
        protected readonly Value $initialValue,
        protected readonly string $valueClass
    ) {
    }

    /**
     * @return array{file: string|null, remove: bool}
     */
    public function getDefaultProperties(): array
    {
        return [
            'file' => null,
            'remove' => false,
        ];
    }

    /**
     * @param array<string, mixed> $value
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function getReverseTransformedValue(array $value): Value
    {
        if ($value['remove']) {
            return $this->fieldType->getEmptyValue();
        }

        /* in case the file is not modified, overwrite settings only */
        $file = $value['file'];
        if (null === $file) {
            return clone $this->initialValue;
        }

        $properties = [
            'inputUri' => $file->getRealPath(),
            'fileName' => $file->getClientOriginalName(),
            'fileSize' => $file->getSize(),
        ];

        return new $this->valueClass($properties);
    }
}
