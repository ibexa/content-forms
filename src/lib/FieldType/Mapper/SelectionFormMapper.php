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
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SelectionFormMapper implements FieldValueFormMapperInterface
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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

        $placeholder = $this->translator->trans(
            /** @Desc("None") */
            'content.field_type.ezselection.none',
            [],
            'ibexa_content_forms_fieldtype',
        );

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
                                   'placeholder' => $placeholder,
                               ]
                           )
                           ->setAutoInitialize(false)
                           ->getForm()
            );
    }
}

class_alias(SelectionFormMapper::class, 'EzSystems\EzPlatformContentForms\FieldType\Mapper\SelectionFormMapper');
