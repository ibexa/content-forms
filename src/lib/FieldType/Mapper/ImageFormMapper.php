<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\Mapper;

use Ibexa\ContentForms\FieldType\DataTransformer\ImageValueTransformer;
use Ibexa\ContentForms\Form\Type\FieldType\ImageFieldType;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Core\FieldType\Image\Value;
use Symfony\Component\Form\FormInterface;

final readonly class ImageFormMapper implements FieldValueFormMapperInterface
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

        $isAlternativeTextRequired = $fieldDefinition->getValidatorConfiguration()['AlternativeTextValidator']['required'] ?? false;

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        ImageFieldType::class,
                        [
                            'required' => $fieldDefinition->isRequired(),
                            'label' => $fieldDefinition->getName(),
                            'is_alternative_text_required' => $isAlternativeTextRequired,
                        ]
                    )
                    ->addModelTransformer(
                        new ImageValueTransformer($fieldType, $data->getValue(), Value::class)
                    )
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}
