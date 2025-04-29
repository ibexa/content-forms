<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Content\Form\Provider;

use Ibexa\ContentForms\Content\Form\Provider\GroupedContentFormFieldsProvider;

final class GroupedContentFormFieldsProviderTest extends AbstractGroupedContentFormFieldsProviderTest
{
    public function testGetGroupedFields(): void
    {
        $fieldsGroupsListMock = $this->getFieldsGroupsListMock();
        $fieldsGroupsListMock
            ->expects(self::once())
            ->method('getGroups')
            ->willReturn([
                'group_1' => 'Group 1',
                'group_2' => 'Group 2',
            ]);

        $subject = new GroupedContentFormFieldsProvider($fieldsGroupsListMock);
        $result = $subject->getGroupedFields($this->getTestForms());

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
}
