<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Validator\Constraints;

use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Core\FieldType\ValidationError;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Util\PropertyPath;

/**
 * Base class for field value validators.
 */
final class FieldValueValidator extends FieldTypeValidator
{
    /**
     * @param \Ibexa\Contracts\ContentForms\Data\Content\FieldData $value
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof FieldData) {
            return;
        }

        $fieldValue = $this->getFieldValue($value);

        $fieldTypeIdentifier = $this->getFieldTypeIdentifier($value);
        $fieldDefinition = $this->getFieldDefinition($value);
        $fieldType = $this->fieldTypeService->getFieldType($fieldTypeIdentifier);

        if ($fieldDefinition->isRequired() && ($fieldValue === null || $fieldType->isEmptyValue($fieldValue))) {
            $validationErrors = [
                new ValidationError(
                    "Value for required field definition '%identifier%' with language '%languageCode%' is empty",
                    null,
                    [
                        '%identifier%' => $fieldDefinition->getIdentifier(),
                        '%languageCode%' => $value->getField()->getLanguageCode(),
                    ],
                    'empty'
                ),
            ];
        } elseif ($fieldValue !== null) {
            $validationErrors = $fieldType->validateValue($fieldDefinition, $fieldValue);
        } else {
            $validationErrors = [];
        }

        $this->processValidationErrors(
            iterator_to_array($validationErrors)
        );
    }

    /**
     * Returns the field value to validate.
     */
    protected function getFieldValue(FieldData $value): ?Value
    {
        return $value->value;
    }

    /**
     * Returns the field definition $value refers to.
     * FieldDefinition object is needed to validate field value against field settings.
     */
    protected function getFieldDefinition(FieldData $value): FieldDefinition
    {
        return $value->getFieldDefinition();
    }

    /**
     * Returns the fieldTypeIdentifier for the field value to validate.
     *
     * @param \Ibexa\Contracts\ContentForms\Data\Content\FieldData $value fieldData ValueObject holding the field value to validate
     */
    protected function getFieldTypeIdentifier(ValueObject $value): string
    {
        return $value->getFieldDefinition()->getFieldTypeIdentifier();
    }

    protected function generatePropertyPath(int $errorIndex, ?string $errorTarget): string
    {
        $basePath = 'value';

        return $errorTarget === null
            ? $basePath
            : PropertyPath::append($basePath, $errorTarget);
    }
}
