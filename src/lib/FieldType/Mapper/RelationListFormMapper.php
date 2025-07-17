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

final class RelationListFormMapper extends AbstractRelationFormMapper
{
    /**
     * @param \Symfony\Component\Form\FormInterface<mixed> $fieldForm
     */
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data): void
    {
        $fieldDefinition = $data->getFieldDefinition();
        $formConfig = $fieldForm->getConfig();
        $fieldSettings = $fieldDefinition->getFieldSettings();

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        RelationListFieldType::class,
                        [
                            'required' => $fieldDefinition->isRequired(),
                            'label' => $fieldDefinition->getName(),
                            'default_location' => $this->loadDefaultLocationForSelection(
                                isset($fieldSettings['selectionDefaultLocation'])
                                    ? (int)$fieldSettings['selectionDefaultLocation']
                                    : null,
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
