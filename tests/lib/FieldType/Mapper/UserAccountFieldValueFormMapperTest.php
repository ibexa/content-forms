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

final class UserAccountFieldValueFormMapperTest extends BaseMapperTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $data = new UserCreateData();
        $data->contentType = $this->createMock(ContentType::class);

        $formRoot = $this->createMock(FormInterface::class);
        $formRoot
            ->method('getData')
            ->willReturn($data);

        $userEditForm = $this->createMock(FormInterface::class);
        $config = $this->createMock(FormConfigInterface::class);

        $config
            ->method('getOption')
            ->with('intent')
            ->willReturn('update');

        $formRoot
            ->method('getConfig')
            ->willReturn($config);

        $userEditForm
            ->method('getRoot')
            ->willReturn($formRoot);

        $this->fieldForm
            ->method('getRoot')
            ->willReturn($userEditForm);
    }

    public function testMapFieldValueFormNoLanguageCode(): void
    {
        $mapper = new UserAccountFieldValueFormMapper();

        $fieldDefinition = new FieldDefinition(['names' => []]);

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
        $mapper = new UserAccountFieldValueFormMapper();

        $fieldDefinition = new FieldDefinition(['names' => ['eng-GB' => 'foo']]);

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
