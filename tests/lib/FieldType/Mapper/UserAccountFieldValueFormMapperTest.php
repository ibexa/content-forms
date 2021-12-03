<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\FieldType\Mapper;

use Ibexa\ContentForms\Data\User\UserCreateData;
use Ibexa\ContentForms\FieldType\Mapper\UserAccountFieldValueFormMapper;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;

class UserAccountFieldValueFormMapperTest extends BaseMapperTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $data = new UserCreateData();
        $data->contentType = $this->createMock(ContentType::class);

        $formRoot = $this->getMockBuilder(FormInterface::class)->getMock();
        $formRoot
            ->method('getData')
            ->willReturn($data);

        $userEditForm = $this->getMockBuilder(FormInterface::class)->getMock();
        $config = $this->getMockBuilder(FormConfigInterface::class)->getMock();

        $config->method('getOption')
            ->with('intent')
            ->willReturn('update');
        $formRoot->method('getConfig')
            ->willReturn($config);
        $userEditForm->method('getRoot')
            ->willReturn($formRoot);

        $this->fieldForm->method('getRoot')
            ->willReturn($userEditForm);
    }

    public function testMapFieldValueFormNoLanguageCode()
    {
        $mapper = new UserAccountFieldValueFormMapper();

        $fieldDefinition = new FieldDefinition(['names' => []]);

        $this->data->expects($this->once())
            ->method('__get')
            ->with('fieldDefinition')
            ->willReturn($fieldDefinition);

        $this->config
            ->method('getOption')
            ->willReturnMap([
                ['languageCode', null, 'eng-GB'],
                ['mainLanguageCode', null, 'eng-GB'],
            ]);

        $mapper->mapFieldValueForm($this->fieldForm, $this->data);
    }

    public function testMapFieldValueFormWithLanguageCode()
    {
        $mapper = new UserAccountFieldValueFormMapper();

        $fieldDefinition = new FieldDefinition(['names' => ['eng-GB' => 'foo']]);

        $this->data->expects($this->once())
            ->method('__get')
            ->with('fieldDefinition')
            ->willReturn($fieldDefinition);

        $this->config
            ->method('getOption')
            ->with('languageCode')
            ->willReturn('eng-GB');

        $mapper->mapFieldValueForm($this->fieldForm, $this->data);
    }
}

class_alias(UserAccountFieldValueFormMapperTest::class, 'EzSystems\EzPlatformContentForms\Tests\FieldType\Mapper\UserAccountFieldValueFormMapperTest');
