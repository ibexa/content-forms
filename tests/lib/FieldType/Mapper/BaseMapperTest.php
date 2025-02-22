<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\FieldType\Mapper;

use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\FieldType;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

abstract class BaseMapperTest extends TestCase
{
    protected FieldTypeService & MockObject $fieldTypeService;

    protected FormConfigInterface & MockObject $config;

    protected FormInterface & MockObject $fieldForm;

    protected FieldData & MockObject $data;

    #[Override]
    protected function setUp(): void
    {
        $this->fieldTypeService = $this->getMockBuilder(FieldTypeService::class)
            ->getMock();
        $this->fieldTypeService
            ->method('getFieldType')
            ->willReturn($this->getMockBuilder(FieldType::class)->getMock());

        $this->config = $this->getMockBuilder(FormConfigInterface::class)->getMock();

        $this->fieldForm = $this->createMock(FormInterface::class);
        $formBuilder = $this->createMock(FormBuilder::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(self::once())
            ->method('createBuilder')
            ->willReturn($formBuilder);

        $this->config->expects(self::once())
            ->method('getFormFactory')
            ->willReturn($formFactory);

        $this->fieldForm->expects(self::once())
            ->method('getConfig')
            ->willReturn($this->config);

        $this->data = $this->getMockBuilder(FieldData::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
