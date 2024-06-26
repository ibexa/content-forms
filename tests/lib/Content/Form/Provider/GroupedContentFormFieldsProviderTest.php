<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Content\Form\Provider;

use Ibexa\ContentForms\Content\Form\Provider\GroupedContentFormFieldsProvider;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Core\FieldType\TextLine\Value;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;

final class GroupedContentFormFieldsProviderTest extends TestCase
{
    public function testGetGroupedFields(): void
    {
        $fieldsGroupsListMock = $this->createMock(FieldsGroupsList::class);
        $fieldsGroupsListMock
            ->expects(self::exactly(3))
            ->method('getFieldGroup')
            ->withConsecutive()
            ->willReturnOnConsecutiveCalls('group_1', 'group_2', 'group_2');

        $fieldsGroupsListMock
            ->expects(self::once())
            ->method('getGroups')
            ->willReturn([
                'group_1' => 'Group 1',
                'group_2' => 'Group 2',
            ]);

        $subject = new GroupedContentFormFieldsProvider($fieldsGroupsListMock);

        $form1 = $this->getFormMockWithFieldData(
            'first_field',
            'first_field_type',
        );

        $form2 = $this->getFormMockWithFieldData(
            'second_field',
            'second_field_type',
        );

        $form3 = $this->getFormMockWithFieldData(
            'third_field',
            'third_field_type',
        );

        $result = $subject->getGroupedFields([$form1, $form2, $form3]);

        $expected = [
            'Group 1' => [
                0 => 'first_field',
            ],
            'Group 2' => [
                0 => 'second_field',
                1 => 'third_field',
            ],
        ];

        self::assertEquals($expected, $result);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getFormMockWithFieldData(
        string $fieldDefIdentifier,
        string $fieldTypeIdentifier
    ) {
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
}
