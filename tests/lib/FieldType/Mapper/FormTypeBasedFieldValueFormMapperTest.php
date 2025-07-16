<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\FieldType\Mapper;

use Ibexa\ContentForms\FieldType\Mapper\FormTypeBasedFieldValueFormMapper;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class FormTypeBasedFieldValueFormMapperTest extends BaseMapperTest
{
    public function testMapFieldValueFormNoLanguageCode(): void
    {
        $mapper = new FormTypeBasedFieldValueFormMapper($this->fieldTypeService);
        $mapper->setFormType(TextType::class);

        $fieldDefinition = new FieldDefinition([
            'names' => [],
            'isRequired' => false,
            'fieldTypeIdentifier' => 'ibexa_selection',
            'fieldSettings' => ['isMultiple' => false, 'options' => []],
        ]);

        $this->data
            ->expects(self::once())
            ->method('getFieldDefinition')
            ->willReturn($fieldDefinition);

        $this->config
            ->method('getOption')
            ->willReturnMap([
                ['languageCode', null, 'eng-GB'],
                ['mainLanguageCode', null, 'eng-GB'],
            ]);

        $mapper->mapFieldValueForm($this->fieldForm, $this->data);
    }

    public function testMapFieldValueFormWithLanguageCode(): void
    {
        $mapper = new FormTypeBasedFieldValueFormMapper($this->fieldTypeService);
        $mapper->setFormType(TextType::class);

        $fieldDefinition = new FieldDefinition([
            'names' => ['eng-GB' => 'foo'],
            'isRequired' => false,
            'fieldTypeIdentifier' => 'ibexa_selection',
            'fieldSettings' => ['isMultiple' => false, 'options' => []],
        ]);

        $this->data
            ->expects(self::once())
            ->method('getFieldDefinition')
            ->willReturn($fieldDefinition);

        $this->config
            ->method('getOption')
            ->with('languageCode')
            ->willReturn('eng-GB');

        $mapper->mapFieldValueForm($this->fieldForm, $this->data);
    }
}
