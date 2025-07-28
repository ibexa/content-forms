<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\Mapper;

use Ibexa\ContentForms\Data\Content\ContentCreateData;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form data mapper for content create without a draft.
 */
final readonly class ContentCreateMapper implements FormDataMapperInterface
{
    /**
     * Maps a ValueObject from Ibexa content repository to a data usable as underlying form data (e.g. create/update struct).
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $repositoryValueObject
     * @param array<string, mixed> $params
     */
    public function mapToFormData(ValueObject $repositoryValueObject, array $params = []): ContentCreateData
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $params = $resolver->resolve($params);

        $data = new ContentCreateData([
            'contentType' => $repositoryValueObject,
            'mainLanguageCode' => $params['mainLanguageCode'],
        ]);
        $data->addLocationStruct($params['parentLocation']);

        foreach ($repositoryValueObject->getFieldDefinitions() as $fieldDef) {
            $data->addFieldData(new FieldData([
                'fieldDefinition' => $fieldDef,
                'field' => new Field([
                    'fieldDefIdentifier' => $fieldDef->getIdentifier(),
                    'languageCode' => $params['mainLanguageCode'],
                ]),
                'value' => $fieldDef->getDefaultValue(),
            ]));
        }

        return $data;
    }

    private function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver
            ->setRequired(['mainLanguageCode', 'parentLocation'])
            ->setAllowedTypes('parentLocation', LocationCreateStruct::class);
    }
}
