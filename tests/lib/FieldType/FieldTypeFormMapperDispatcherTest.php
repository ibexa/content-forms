<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\FieldType;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\ContentForms\FieldType\FieldTypeFormMapperDispatcher;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;

class FieldTypeFormMapperDispatcherTest extends TestCase
{
    /** @var \EzSystems\EzPlatformContentForms\FieldType\FieldTypeFormMapperDispatcherInterface */
    private $dispatcher;

    /** @var \EzSystems\EzPlatformContentForms\FieldType\FieldValueFormMapperInterface|\PHPUnit\Framework\MockObject\MockObject */
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