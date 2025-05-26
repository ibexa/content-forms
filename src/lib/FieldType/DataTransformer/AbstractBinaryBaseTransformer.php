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
    protected FieldType $fieldType;

    protected Value $initialValue;

    /** @phpstan-var class-string */
    protected string $valueClass;

    /**
     * @phpstan-param class-string $valueClass
     */
    public function __construct(FieldType $fieldType, Value $initialValue, string $valueClass)
    {
        $this->fieldType = $fieldType;
        $this->initialValue = $initialValue;
        $this->valueClass = $valueClass;
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
        if (null === $value['file']) {
            return clone $this->initialValue;
        }

        $properties = [
            'inputUri' => $value['file']->getRealPath(),
            'fileName' => $value['file']->getClientOriginalName(),
            'fileSize' => $value['file']->getSize(),
        ];

        return new $this->valueClass($properties);
    }
}
