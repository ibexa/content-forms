<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\Mapper;

use Ibexa\ContentForms\FieldType\DataTransformer\MediaValueTransformer;
use Ibexa\ContentForms\Form\Type\FieldType\MediaFieldType;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Core\FieldType\Media\Type;
use Ibexa\Core\FieldType\Media\Value;
use Symfony\Component\Form\FormInterface;

final readonly class MediaFormMapper implements FieldValueFormMapperInterface
{
    private const string ACCEPT_VIDEO = 'video/*';
    private const string ACCEPT_AUDIO = 'audio/*';

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

        $acceptedFormat = Type::TYPE_HTML5_AUDIO === $fieldDefinition->getFieldSettings()['mediaType']
            ? self::ACCEPT_AUDIO
            : self::ACCEPT_VIDEO;

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        MediaFieldType::class,
                        [
                            'required' => $fieldDefinition->isRequired(),
                            'label' => $fieldDefinition->getName(),
                            'attr' => [
                                'accept' => $acceptedFormat,
                            ],
                        ]
                    )
                    ->addModelTransformer(
                        new MediaValueTransformer($fieldType, $data->getValue(), Value::class)
                    )
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}
