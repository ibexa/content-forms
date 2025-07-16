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

final readonly class ContentUpdateMapper implements FormDataMapperInterface
{
    /**
     * Maps a ValueObject from Ibexa content repository to a data usable as underlying form data (e.g. create/update struct).
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $repositoryValueObject
     * @param array<string, mixed> $params
     */
    public function mapToFormData(ValueObject $repositoryValueObject, array $params = []): ContentUpdateData
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);

        $params = $optionsResolver->resolve($params);
        $languageCode = $params['languageCode'];
        $currentFields = $params['currentFields'];
        $mappedCurrentFields = array_column($currentFields, null, 'fieldDefIdentifier');

        $data = new ContentUpdateData(['contentDraft' => $repositoryValueObject]);
        $data->initialLanguageCode = $languageCode;

        $fields = $repositoryValueObject->getFieldsByLanguage($languageCode);
        $mainLanguageCode = $repositoryValueObject
            ->getVersionInfo()
            ->getContentInfo()
            ->getMainLanguage()
            ->getLanguageCode();

        foreach ($params['contentType']->getFieldDefinitions() as $fieldDef) {
            $isNonTranslatable = $fieldDef->isTranslatable() === false;
            $field = $fields[$fieldDef->getIdentifier()];
            $fieldDefIdentifier = $fieldDef->getIdentifier();

            $shouldUseCurrentFieldValue = $isNonTranslatable
                && isset($mappedCurrentFields[$fieldDefIdentifier])
                && $mainLanguageCode !== $languageCode;

            $data->addFieldData(new FieldData([
                'fieldDefinition' => $fieldDef,
                'field' => $field,
                'value' => $shouldUseCurrentFieldValue
                    ? $mappedCurrentFields[$fieldDefIdentifier]->getValue()
                    : $field->getValue(),
            ]));
        }

        return $data;
    }

    private function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver
            ->setRequired(['languageCode', 'contentType', 'currentFields'])
            ->setAllowedTypes('contentType', ContentType::class)
            ->setDefault('currentFields', []);
    }
}
