<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\Mapper;

use Ibexa\ContentForms\Form\Type\FieldType\RelationListFieldType;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Symfony\Component\Form\FormInterface;

class RelationListFormMapper extends AbstractRelationFormMapper
{
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data)
    {
        $fieldDefinition = $data->fieldDefinition;
        $formConfig = $fieldForm->getConfig();
        $fieldSettings = $fieldDefinition->getFieldSettings();

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        RelationListFieldType::class,
                        [
                            'required' => $fieldDefinition->isRequired,
                            'label' => $fieldDefinition->getName(),
                            'default_location' => $this->loadDefaultLocationForSelection(
                                $fieldSettings['selectionDefaultLocation'],
                                $fieldForm->getConfig()->getOption('location'),
                            ),
                            'root_default_location' => $fieldSettings['rootDefaultLocation'] ?? false,
                        ]
                    )
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}

class_alias(RelationListFormMapper::class, 'EzSystems\EzPlatformContentForms\FieldType\Mapper\RelationListFormMapper');
