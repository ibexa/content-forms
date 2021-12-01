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

class SelectionFormMapper implements FieldValueFormMapperInterface
{
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data)
    {
        $fieldDefinition = $data->fieldDefinition;
        $formConfig = $fieldForm->getConfig();
        $languageCode = $fieldForm->getConfig()->getOption('languageCode');

        $choices = $fieldDefinition->fieldSettings['options'];

        if (!empty($fieldDefinition->fieldSettings['multilingualOptions'][$languageCode])) {
            $choices = $fieldDefinition->fieldSettings['multilingualOptions'][$languageCode];
        } elseif (!empty($fieldDefinition->fieldSettings['multilingualOptions'][$fieldDefinition->mainLanguageCode])) {
            $choices = $fieldDefinition->fieldSettings['multilingualOptions'][$fieldDefinition->mainLanguageCode];
        }

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                           ->create(
                               'value',
                               SelectionFieldType::class,
                               [
                                   'required' => $fieldDefinition->isRequired,
                                   'label' => $fieldDefinition->getName(),
                                   'multiple' => $fieldDefinition->fieldSettings['isMultiple'],
                                   'choices' => array_flip($choices),
                               ]
                           )
                           ->setAutoInitialize(false)
                           ->getForm()
            );
    }
}

class_alias(SelectionFormMapper::class, 'EzSystems\EzPlatformContentForms\FieldType\Mapper\SelectionFormMapper');
