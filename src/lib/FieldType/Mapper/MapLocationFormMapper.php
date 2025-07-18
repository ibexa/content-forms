<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\Mapper;

use Ibexa\ContentForms\Form\Type\FieldType\MapLocationFieldType;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Symfony\Component\Form\FormInterface;

/**
 * FormMapper for ibexa_gmap_location FieldType.
 */
final readonly class MapLocationFormMapper implements FieldValueFormMapperInterface
{
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data): void
    {
        $fieldDefinition = $data->getFieldDefinition();
        $formConfig = $fieldForm->getConfig();

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        MapLocationFieldType::class,
                        [
                            'required' => $fieldDefinition->isRequired(),
                            'label' => $fieldDefinition->getName(),
                        ]
                    )
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}
