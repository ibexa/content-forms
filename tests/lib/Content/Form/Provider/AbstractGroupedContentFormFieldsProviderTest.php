<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Content\Form\Provider;

use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Core\FieldType\TextLine\Value;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;

abstract class AbstractGroupedContentFormFieldsProviderTest extends TestCase
{
    final protected function getFieldsGroupsListMock(): FieldsGroupsList&MockObject
    {
        $mock = $this->createMock(FieldsGroupsList::class);
        $matcher = self::exactly(3);
        $expectedGroups = [1 => 'group_1', 2 => 'group_2', 3 => 'group_2'];

        $mock
            ->expects($matcher)
            ->method('getFieldGroup')
            ->willReturnCallback(static function () use ($matcher, $expectedGroups): string {
                return $expectedGroups[$matcher->getInvocationCount()];
            });

        return $mock;
    }

    final protected function getFormMockWithFieldData(
        string $fieldDefIdentifier,
        string $fieldTypeIdentifier
    ): FormInterface {
        $formMock = $this
            ->getMockBuilder(FormInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $formMock
            ->expects(self::once())
            ->method('getViewData')
            ->willReturn(new FieldData([
                'field' => new Field(['fieldDefIdentifier' => $fieldDefIdentifier]),
                'fieldDefinition' => new FieldDefinition(['fieldTypeIdentifier' => $fieldTypeIdentifier]),
                'value' => new Value('value'),
            ]));

        $formMock
            ->expects(self::once())
            ->method('getName')
            ->willReturn($fieldDefIdentifier);

        return $formMock;
    }

    final protected function getTestForms(): array
    {
        return [
            $this->getFormMockWithFieldData('first_field', 'first_field_type'),
            $this->getFormMockWithFieldData('second_field', 'second_field_type'),
            $this->getFormMockWithFieldData('third_field', 'third_field_type'),
        ];
    }
}
