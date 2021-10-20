<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\Mapper;

use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\Core\FieldType\Media\Type;
use eZ\Publish\Core\FieldType\Media\Value;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\ContentForms\FieldType\DataTransformer\MediaValueTransformer;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Ibexa\ContentForms\Form\Type\FieldType\MediaFieldType;
use Symfony\Component\Form\FormInterface;

class MediaFormMapper implements FieldValueFormMapperInterface
{
    /** @var FieldTypeService */
    private $fieldTypeService;

    protected const ACCEPT_VIDEO = 'video/*';
    protected const ACCEPT_AUDIO = 'audio/*';

    public function __construct(FieldTypeService $fieldTypeService)
    {
        $this->fieldTypeService = $fieldTypeService;
    }

    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data)
    {
        $fieldDefinition = $data->fieldDefinition;
        $formConfig = $fieldForm->getConfig();
        $fieldType = $this->fieldTypeService->getFieldType($fieldDefinition->fieldTypeIdentifier);

        $acceptedFormat = Type::TYPE_HTML5_AUDIO === $fieldDefinition->fieldSettings['mediaType']
            ? self::ACCEPT_AUDIO
            : self::ACCEPT_VIDEO;

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        MediaFieldType::class,
                        [
                            'required' => $fieldDefinition->isRequired,
                            'label' => $fieldDefinition->getName(),
                            'attr' => [
                                'accept' => $acceptedFormat,
                            ],
                        ]
                    )
                    ->addModelTransformer(new MediaValueTransformer($fieldType, $data->value, Value::class))
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}

class_alias(MediaFormMapper::class, 'EzSystems\EzPlatformContentForms\FieldType\Mapper\MediaFormMapper');
