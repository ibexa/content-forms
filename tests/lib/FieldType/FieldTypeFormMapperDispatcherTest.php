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
use Ibexa\Core\FieldType\FieldTypeAliasResolverInterface;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;

final class FieldTypeFormMapperDispatcherTest extends TestCase
{
    private FieldTypeFormMapperDispatcher $dispatcher;

    private FieldTypeAliasResolverInterface & MockObject $fieldTypeAliasResolverMock;

    private FieldValueFormMapperInterface & MockObject $fieldValueMapperMock;

    protected function setUp(): void
    {
        $this->fieldValueMapperMock = $this->createMock(FieldValueFormMapperInterface::class);
        $this->fieldTypeAliasResolverMock = $this->createMock(FieldTypeAliasResolverInterface::class);

        $this->dispatcher = new FieldTypeFormMapperDispatcher($this->fieldTypeAliasResolverMock);
        $this->dispatcher->addMapper($this->fieldValueMapperMock, 'first_type');
    }

    public function testMapFieldValue(): void
    {
        $data = new FieldData([
            'field' => new Field(['fieldDefIdentifier' => 'first_type']),
            'fieldDefinition' => new FieldDefinition(['fieldTypeIdentifier' => 'first_type']),
        ]);

        $formMock = $this->createMock(FormInterface::class);

        $this->fieldTypeAliasResolverMock
            ->method('resolveIdentifier')
            ->willReturnArgument(0);

        $this->fieldValueMapperMock
            ->expects(self::once())
            ->method('mapFieldValueForm')
            ->with($formMock, $data);

        $this->dispatcher->map($formMock, $data);
    }
}
