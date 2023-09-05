<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Content\Form\Provider;

use Ibexa\Contracts\ContentForms\Content\Form\Provider\GroupedContentFormFieldsProviderInterface;
use Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

final class GroupedContentFormFieldsProvider implements GroupedContentFormFieldsProviderInterface, TranslationContainerInterface
{
    /** @var \Ibexa\Core\Helper\FieldsGroups\FieldsGroupsList */
    private $fieldsGroupsList;

    public function __construct(FieldsGroupsList $fieldsGroupsList)
    {
        $this->fieldsGroupsList = $fieldsGroupsList;
    }

    public function getGroupedFields(array $fieldsDataForm): array
    {
        $fieldsGroups = $this->fieldsGroupsList->getGroups();
        $groupedFields = [];

        foreach ($fieldsDataForm as $fieldForm) {
            /** @var \Ibexa\Contracts\ContentForms\Data\Content\FieldData $fieldData */
            $fieldData = $fieldForm->getViewData();
            $fieldGroupIdentifier = $this->fieldsGroupsList->getFieldGroup($fieldData->fieldDefinition);
            $fieldGroupName = $fieldsGroups[$fieldGroupIdentifier] ?? $this->fieldsGroupsList->getDefaultGroup();

            $groupedFields[$fieldGroupName][] = $fieldForm->getName();
        }

        return $groupedFields;
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create('content', 'ibexa_fields_groups')->setDesc('Content'),
            Message::create('metadata', 'ibexa_fields_groups')->setDesc('Metadata'),
        ];
    }
}
