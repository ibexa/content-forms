<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\Mapper;

use Ibexa\ContentForms\FieldType\DataTransformer\BinaryFileValueTransformer;
use Ibexa\ContentForms\Form\Type\FieldType\BinaryFileFieldType;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Core\FieldType\BinaryFile\Value;
use Symfony\Component\Form\FormInterface;

final readonly class BinaryFileFormMapper implements FieldValueFormMapperInterface
{
    public function __construct(private FieldTypeService $fieldTypeService)
    {
    }

    /**
     * @param \Symfony\Component\Form\FormInterface<mixed> $fieldForm
     */
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data): void
    {
        $fieldDefinition = $data->getFieldDefinition();
        $formConfig = $fieldForm->getConfig();
        $fieldType = $this->fieldTypeService->getFieldType(
            $fieldDefinition->getFieldTypeIdentifier()
        );

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        BinaryFileFieldType::class,
                        [
                            'required' => $fieldDefinition->isRequired(),
                            'label' => $fieldDefinition->getName(),
                        ]
                    )
                    ->addModelTransformer(
                        new BinaryFileValueTransformer($fieldType, $data->getValue(), Value::class)
                    )
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}
