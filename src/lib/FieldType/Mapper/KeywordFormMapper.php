<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\Mapper;

use Ibexa\ContentForms\Form\Type\FieldType\KeywordFieldType;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Symfony\Component\Form\FormInterface;

/**
 * FormMapper for ezkeyword FieldType.
 */
class KeywordFormMapper implements FieldValueFormMapperInterface
{
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data)
    {
        $fieldDefinition = $data->fieldDefinition;
        $formConfig = $fieldForm->getConfig();

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        KeywordFieldType::class,
                        [
                            'required' => $fieldDefinition->isRequired,
                            'label' => $fieldDefinition->getName(),
                        ]
                    )
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}

class_alias(KeywordFormMapper::class, 'EzSystems\EzPlatformContentForms\FieldType\Mapper\KeywordFormMapper');
