<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\Mapper;

use Ibexa\ContentForms\Data\Content\ContentUpdateData;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentUpdateMapper implements FormDataMapperInterface
{
    /**
     * Maps a ValueObject from Ibexa content repository to a data usable as underlying form data (e.g. create/update struct).
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content|\Ibexa\Contracts\Core\Repository\Values\ValueObject $contentDraft
     * @param array $params
     *
     * @return \Ibexa\ContentForms\Data\Content\ContentUpdateData
     */
    public function mapToFormData(ValueObject $contentDraft, array $params = [])
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);

        $params = $optionsResolver->resolve($params);
        $languageCode = $params['languageCode'];
        $currentFields = $params['currentFields'];
        $mappedCurrentFields = array_column($currentFields, null, 'fieldDefIdentifier');

        $data = new ContentUpdateData(['contentDraft' => $contentDraft]);
        $data->initialLanguageCode = $languageCode;

        $fields = $contentDraft->getFieldsByLanguage($languageCode);
        $mainLanguageCode = $contentDraft->getVersionInfo()->getContentInfo()->getMainLanguage()->getLanguageCode();

        foreach ($params['contentType']->fieldDefinitions as $fieldDef) {
            $isNonTranslatable = $fieldDef->isTranslatable === false;
            $field = $fields[$fieldDef->identifier];
            $shouldUseCurrentFieldValue = $isNonTranslatable
                && isset($mappedCurrentFields[$fieldDef->identifier])
                && $mainLanguageCode !== $languageCode;
            $data->addFieldData(new FieldData([
                'fieldDefinition' => $fieldDef,
                'field' => $field,
                'value' => $shouldUseCurrentFieldValue
                    ? $mappedCurrentFields[$fieldDef->identifier]->value
                    : $field->value,
            ]));
        }

        return $data;
    }

    private function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver
            ->setRequired(['languageCode', 'contentType', 'currentFields'])
            ->setAllowedTypes('contentType', ContentType::class)
            ->setDefault('currentFields', []);
    }
}

class_alias(ContentUpdateMapper::class, 'EzSystems\EzPlatformContentForms\Data\Mapper\ContentUpdateMapper');
