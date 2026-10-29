<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Content\Form\Provider;

use Ibexa\ContentForms\Content\Form\Provider\IdentifiedGroupedContentFormFieldsProvider;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition as APIFieldDefinition;
use Ibexa\Core\FieldType\TextLine\Value;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;

#[CoversClass(IdentifiedGroupedContentFormFieldsProvider::class)]
final class IdentifiedGroupedContentFormFieldsProviderTest extends TestCase
{
    private const array FIELD_GROUPS = [
        'first_field' => 'group_1',
        'second_field' => 'group_2',
        'third_field' => 'group_2',
    ];

    public function testGetGroupedFields(): void
    {
        $fieldsGroupsList = self::createStub(FieldsGroupsList::class);
        $fieldsGroupsList
            ->method('getFieldGroup')
            ->willReturnCallback(
                static fn (APIFieldDefinition $fieldDefinition): string => self::FIELD_GROUPS[$fieldDefinition->identifier]
            );

        $subject = new IdentifiedGroupedContentFormFieldsProvider($fieldsGroupsList);
        $result = $subject->getGroupedFields([
            self::createFieldForm('first_field'),
            self::createFieldForm('second_field'),
            self::createFieldForm('third_field'),
        ]);

        self::assertSame(
            [
                'group_1' => ['first_field'],
                'group_2' => ['second_field', 'third_field'],
            ],
            $result
        );
    }

    /**
     * @return FormInterface<FieldData>
     */
    private static function createFieldForm(string $fieldDefIdentifier): FormInterface
    {
        $form = self::createStub(FormInterface::class);
        $form->method('getViewData')->willReturn(new FieldData([
            'field' => new Field(['fieldDefIdentifier' => $fieldDefIdentifier]),
            'fieldDefinition' => new FieldDefinition(['identifier' => $fieldDefIdentifier]),
            'value' => new Value('value'),
        ]));
        $form->method('getName')->willReturn($fieldDefIdentifier);

        return $form;
    }
}
