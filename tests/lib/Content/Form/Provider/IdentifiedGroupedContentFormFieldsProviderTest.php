<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Content\Form\Provider;

use Ibexa\ContentForms\Content\Form\Provider\IdentifiedGroupedContentFormFieldsProvider;

final class IdentifiedGroupedContentFormFieldsProviderTest extends AbstractGroupedContentFormFieldsProviderTest
{
    public function testGetGroupedFields(): void
    {
        $fieldsGroupsListMock = $this->getFieldsGroupsListMock();

        $subject = new IdentifiedGroupedContentFormFieldsProvider($fieldsGroupsListMock);
        $result = $subject->getGroupedFields($this->getTestForms());

        $expected = [
            'group_1' => [
                0 => 'first_field',
            ],
            'group_2' => [
                0 => 'second_field',
                1 => 'third_field',
            ],
        ];

        self::assertEquals($expected, $result);
    }
}
