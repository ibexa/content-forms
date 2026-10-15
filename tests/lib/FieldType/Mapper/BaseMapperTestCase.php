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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

abstract class BaseMapperTestCase extends TestCase
{
    protected FieldTypeService & MockObject $fieldTypeService;

    /** @phpstan-var FormConfigInterface<FieldData>&MockObject */
    protected FormConfigInterface & MockObject $config;

    /** @phpstan-var FormInterface<FieldData>&MockObject */
    protected FormInterface & MockObject $fieldForm;

    protected FieldData & MockObject $data;

    protected function setUp(): void
    {
        $this->fieldTypeService = $this->createMock(FieldTypeService::class);
        $this->fieldTypeService
            ->method('getFieldType')
            ->willReturn(self::createStub(FieldType::class));

        $this->config = $this->createMock(FormConfigInterface::class);
        $this->fieldForm = $this->createMock(FormInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory
            ->expects($this->once())
            ->method('createBuilder')
            ->willReturn(self::createStub(FormBuilder::class));

        $this->config
            ->expects($this->once())
            ->method('getFormFactory')
            ->willReturn($formFactory);

        $this->fieldForm
            ->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->config);

        $this->data = $this->createMock(FieldData::class);
    }
}
