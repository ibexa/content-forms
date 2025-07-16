<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\Mapper;

use Ibexa\ContentForms\Data\User\UserCreateData;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final readonly class UserCreateMapper
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[] $parentGroups
     * @param array<string, mixed> $params
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\NoSuchOptionException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function mapToFormData(ContentType $contentType, array $parentGroups, array $params = []): UserCreateData
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $params = $resolver->resolve($params);

        $data = new UserCreateData(['contentType' => $contentType, 'mainLanguageCode' => $params['mainLanguageCode']]);
        $data->setParentGroups($parentGroups);

        foreach ($contentType->getFieldDefinitions() as $fieldDef) {
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

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    private function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setRequired('mainLanguageCode');
    }
}
