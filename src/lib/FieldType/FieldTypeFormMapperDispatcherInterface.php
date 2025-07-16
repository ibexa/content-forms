<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType;

use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Symfony\Component\Form\FormInterface;

/**
 * FieldType mappers dispatcher. Maps Field (definition, value) data to a Form using the appropriate mapper.
 */
interface FieldTypeFormMapperDispatcherInterface
{
    public function addMapper(FieldValueFormMapperInterface $mapper, string $fieldTypeIdentifier): void;

    /**
     * Maps, if a mapper is available for the fieldtype, $data to $form.
     *
     * @throws \InvalidArgumentException If $data is not a FieldData or FieldDefinitionData
     */
    public function map(FormInterface $form, FieldData $data): void;
}
