<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\FieldType;

use Ibexa\ContentForms\FieldType\FieldTypeFormMapperDispatcher;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;

class FieldTypeFormMapperDispatcherTest extends TestCase
{
    /** @var \Ibexa\ContentForms\FieldType\FieldTypeFormMapperDispatcherInterface */
    private $dispatcher;

    /** @var \Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $fieldValueMapperMock;

    protected function setUp(): void
    {
        $this->dispatcher = new FieldTypeFormMapperDispatcher();

        $this->fieldValueMapperMock = $this->createMock(FieldValueFormMapperInterface::class);
        $this->dispatcher->addMapper($this->fieldValueMapperMock, 'first_type');
    }

    public function testMapFieldValue()
    {
        $data = new FieldData([
            'field' => new Field(['fieldDefIdentifier' => 'first_type']),
            'fieldDefinition' => new FieldDefinition(['fieldTypeIdentifier' => 'first_type']),
        ]);

        $formMock = $this->createMock(FormInterface::class);

        $this->fieldValueMapperMock
            ->expects($this->once())
            ->method('mapFieldValueForm')
            ->with($formMock, $data);

        $this->dispatcher->map($formMock, $data);
    }
}

class_alias(FieldTypeFormMapperDispatcherTest::class, 'EzSystems\EzPlatformContentForms\Tests\FieldType\FieldTypeFormMapperDispatcherTest');
