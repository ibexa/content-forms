<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\Mapper;

use Ibexa\ContentForms\FieldType\DataTransformer\FieldValueTransformer;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Symfony\Component\Form\FormInterface;

/**
 * Generic FieldValueFormMapper that uses a named FormType to generate a FieldValue Form.
 *
 * The FieldValueTransformer is used as a model transformer.
 *
 * Example in YAML service definition:
 * ```
 * ezrepoforms.field_type.form_mapper.ibexa_user:
 *   class: "%ezrepoforms.field_type.form_mapper.form_type_based.class%"
 *   parent: ezrepoforms.field_type.form_mapper.form_type_based
 *   tags:
 *     - { name: "ez.formMapper.fieldValue", fieldType: "ibexa_user" }
 *   calls:
 *     - [setFormType, ["ibexa_user"]]
 * ```
 */
final class FormTypeBasedFieldValueFormMapper implements FieldValueFormMapperInterface
{
    /**
     * The FormType used by the mapper. Example: '\Symfony\Component\Form\Extension\Core\Type\TextType'.
     */
    private string $formType;

    public function __construct(private readonly FieldTypeService $fieldTypeService)
    {
    }

    public function setFormType(string $formType): void
    {
        $this->formType = $formType;
    }

    /**
     * Maps Field form to current FieldType based on the configured form type (self::$formType).
     */
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data): void
    {
        $fieldDefinition = $data->getFieldDefinition();
        $formConfig = $fieldForm->getConfig();

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        $this->formType,
                        [
                            'required' => $fieldDefinition->isRequired(),
                            'label' => $fieldDefinition->getName(),
                        ]
                    )
                    ->addModelTransformer(
                        new FieldValueTransformer($this->fieldTypeService->getFieldType(
                            $fieldDefinition->getFieldTypeIdentifier()
                        ))
                    )
                    // Deactivate auto-initialize as we're not on the root form.
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}
