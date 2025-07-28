<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\Mapper;

use Ibexa\ContentForms\Form\Type\FieldType\TextLineFieldType;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Symfony\Component\Form\FormInterface;

/**
 * FormMapper for ibexa_string FieldType.
 */
final readonly class TextLineFormMapper implements FieldValueFormMapperInterface
{
    /**
     * @param \Symfony\Component\Form\FormInterface<mixed> $fieldForm
     */
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data): void
    {
        $fieldDefinition = $data->getFieldDefinition();
        $formConfig = $fieldForm->getConfig();
        $validatorConfiguration = $fieldDefinition->getValidatorConfiguration();

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        TextLineFieldType::class,
                        [
                            'required' => $fieldDefinition->isRequired(),
                            'label' => $fieldDefinition->getName(),
                            'min' => $validatorConfiguration['StringLengthValidator']['minStringLength'],
                            'max' => $validatorConfiguration['StringLengthValidator']['maxStringLength'],
                        ]
                    )
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}
