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
class FieldValueValidator extends FieldTypeValidator
{
    /**
     * @param \Ibexa\Contracts\ContentForms\Data\Content\FieldData $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof FieldData) {
            return;
        }

        $fieldValue = $this->getFieldValue($value);

        $fieldTypeIdentifier = $this->getFieldTypeIdentifier($value);
        $fieldDefinition = $this->getFieldDefinition($value);
        $fieldType = $this->fieldTypeService->getFieldType($fieldTypeIdentifier);

        if ($fieldDefinition->isRequired && ($fieldValue === null || $fieldType->isEmptyValue($fieldValue))) {
            $validationErrors = [
                new ValidationError(
                    "Value for required field definition '%identifier%' with language '%languageCode%' is empty",
                    null,
                    ['%identifier%' => $fieldDefinition->identifier, '%languageCode%' => $value->field->languageCode],
                    'empty'
                ),
            ];
        } elseif ($fieldValue !== null) {
            $validationErrors = $fieldType->validateValue($fieldDefinition, $fieldValue);
        } else {
            $validationErrors = [];
        }

        $this->processValidationErrors($validationErrors);
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
        return $value->fieldDefinition;
    }

    /**
     * Returns the fieldTypeIdentifier for the field value to validate.
     *
     * @param \Ibexa\Contracts\ContentForms\Data\Content\FieldData|\Ibexa\Contracts\Core\Repository\Values\ValueObject $value fieldData ValueObject holding the field value to validate
     *
     * @return string
     */
    protected function getFieldTypeIdentifier(ValueObject $value): string
    {
        return $value->fieldDefinition->fieldTypeIdentifier;
    }

    protected function generatePropertyPath($errorIndex, $errorTarget): string
    {
        $basePath = 'value';

        return $errorTarget === null
            ? $basePath
            : PropertyPath::append($basePath, $errorTarget);
    }
}

class_alias(FieldValueValidator::class, 'EzSystems\EzPlatformContentForms\Validator\Constraints\FieldValueValidator');
