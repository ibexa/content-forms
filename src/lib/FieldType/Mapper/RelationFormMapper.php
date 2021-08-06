<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\Mapper;

use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\ContentForms\Form\Type\FieldType\RelationFieldType;
use Symfony\Component\Form\FormInterface;

class RelationFormMapper extends AbstractRelationFormMapper
{
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data)
    {
        $fieldDefinition = $data->fieldDefinition;
        $formConfig = $fieldForm->getConfig();

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create('value', RelationFieldType::class, [
                        'required' => $fieldDefinition->isRequired,
                        'label' => $fieldDefinition->getName(),
                        'default_location' => $this->loadDefaultLocationForSelection(
                            $fieldDefinition->getFieldSettings()['selectionRoot']
                        ),
                    ])
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}

class_alias(RelationFormMapper::class, 'EzSystems\EzPlatformContentForms\FieldType\Mapper\RelationFormMapper');
