<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\Mapper;

use Ibexa\ContentForms\Data\Content\ContentUpdateData;
use Ibexa\ContentForms\Form\Type\FieldType\AuthorFieldType;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Symfony\Component\Form\FormInterface;

/**
 * FormMapper for ezauthor FieldType.
 */
class AuthorFormMapper implements FieldValueFormMapperInterface
{
    /**
     * @param \Symfony\Component\Form\FormInterface $fieldForm
     * @param \Ibexa\Contracts\ContentForms\Data\Content\FieldData $data
     */
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data)
    {
        $fieldDefinition = $data->fieldDefinition;
        $fieldSettings = $fieldDefinition->getFieldSettings();
        $formConfig = $fieldForm->getConfig();

        $parent = $fieldForm->getParent();
        $contentForm = $parent instanceof FormInterface ? $parent->getParent() : null;
        $contentUpdateData = null;
        if ($contentForm instanceof FormInterface) {
            $contentUpdateData = $contentForm->getData();
        }

        $creator = null;
        if ($contentUpdateData instanceof ContentUpdateData && isset($contentUpdateData->contentDraft)) {
            $versionInfo = $contentUpdateData->contentDraft->getVersionInfo();
            $creator = $versionInfo->getCreator();
        }

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create('value', AuthorFieldType::class, [
                        'creator' => $creator,
                        'default_author' => $fieldSettings['defaultAuthor'],
                        'required' => $fieldDefinition->isRequired,
                        'label' => $fieldDefinition->getName(),
                    ])
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}

class_alias(AuthorFormMapper::class, 'EzSystems\EzPlatformContentForms\FieldType\Mapper\AuthorFormMapper');
