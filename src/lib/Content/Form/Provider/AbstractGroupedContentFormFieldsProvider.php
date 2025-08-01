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

abstract class AbstractGroupedContentFormFieldsProvider implements GroupedContentFormFieldsProviderInterface, TranslationContainerInterface
{
    public function __construct(protected readonly FieldsGroupsList $fieldsGroupsList)
    {
    }

    /**
     * @param \Ibexa\Contracts\ContentForms\Data\Content\FieldData[] $fieldsDataForm
     *
     * @return array<string, string[]>
     */
    public function getGroupedFields(array $fieldsDataForm): array
    {
        $groupedFields = [];

        foreach ($fieldsDataForm as $fieldForm) {
            /** @var \Ibexa\Contracts\ContentForms\Data\Content\FieldData $fieldData */
            $fieldData = $fieldForm->getViewData();
            $fieldGroupIdentifier = $this->fieldsGroupsList->getFieldGroup($fieldData->getFieldDefinition());
            $groupKey = $this->getGroupKey($fieldGroupIdentifier);

            $groupedFields[$groupKey][] = $fieldForm->getName();
        }

        return $groupedFields;
    }

    abstract protected function getGroupKey(string $fieldGroupIdentifier): string;

    public static function getTranslationMessages(): array
    {
        return [
            Message::create('content', 'ibexa_fields_groups')->setDesc('Content'),
            Message::create('metadata', 'ibexa_fields_groups')->setDesc('Metadata'),
        ];
    }
}
