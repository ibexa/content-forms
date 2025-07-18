<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\Mapper;

use Ibexa\ContentForms\Form\Type\FieldType\SelectionFieldType;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Symfony\Component\Form\FormInterface;

final readonly class SelectionFormMapper implements FieldValueFormMapperInterface
{
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data): void
    {
        $fieldDefinition = $data->getFieldDefinition();
        $formConfig = $fieldForm->getConfig();
        $languageCode = $fieldForm->getConfig()->getOption('languageCode');

        $fieldSettings = $fieldDefinition->getFieldSettings();
        $choices = $fieldSettings['options'];

        if (!empty($fieldSettings['multilingualOptions'][$languageCode])) {
            $choices = $fieldSettings['multilingualOptions'][$languageCode];
        } elseif (!empty($fieldSettings['multilingualOptions'][$fieldDefinition->getMainLanguageCode()])) {
            $choices = $fieldSettings['multilingualOptions'][$fieldDefinition->getMainLanguageCode()];
        }

        $fieldForm
            ->add(
                $formConfig->getFormFactory()
                    ->createBuilder()
                    ->create(
                        'value',
                        SelectionFieldType::class,
                        [
                           'required' => $fieldDefinition->isRequired(),
                           'label' => $fieldDefinition->getName(),
                           'multiple' => $fieldSettings['isMultiple'],
                           'choices' => array_flip($choices),
                       ]
                    )
                   ->setAutoInitialize(false)
                   ->getForm()
            );
    }
}
