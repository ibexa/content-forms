<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\EventSubscriber;

use Ibexa\ContentForms\Data\User\UserCreateData;
use Ibexa\ContentForms\Data\User\UserUpdateData;
use Ibexa\Core\FieldType\User\Value;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Maps data between repository user create/update struct and form data object.
 */
class UserFieldsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'handleUserAccountField',
        ];
    }

    /**
     * Handles User Account field in create/update struct.
     *
     * Workaround to quirky ezuser field type, it copies user data from field Data class to general User update/create
     * struct and injects proper Value for ezuser field type in order to pass validation.
     *
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function handleUserAccountField(FormEvent $event)
    {
        /** @var \Ibexa\ContentForms\Data\User\UserCreateData|\Ibexa\ContentForms\Data\User\UserUpdateData $data */
        $data = $event->getData();
        $form = $event->getForm();
        $languageCode = $form->getConfig()->getOption('languageCode');

        if ($data->isNew()) {
            $this->handleUserCreateData($data);
        } else {
            $this->handleUserUpdateData($data, $languageCode);
        }
    }

    /**
     * @param \Ibexa\ContentForms\Data\User\UserCreateData $data
     */
    private function handleUserCreateData(UserCreateData $data)
    {
        foreach ($data->fieldsData as $fieldData) {
            if ('ezuser' !== $fieldData->getFieldTypeIdentifier()) {
                continue;
            }

            /** @var \Ibexa\ContentForms\Data\User\UserAccountFieldData $userAccountFieldData */
            $userAccountFieldData = $fieldData->value;
            $data->login = $userAccountFieldData->username;
            $data->email = $userAccountFieldData->email;
            $data->password = $userAccountFieldData->password;
            $data->enabled = $userAccountFieldData->enabled ?? $data->enabled;

            /** @var \Ibexa\Core\FieldType\User\Value $userValue */
            $userValue = clone $data->contentType
                ->getFieldDefinition($fieldData->field->fieldDefIdentifier)->defaultValue;
            $userValue->login = $data->login;
            $userValue->email = $data->email;
            $userValue->enabled = $data->enabled;
            $userValue->plainPassword = $data->password;

            $fieldData->value = $userValue;

            return;
        }
    }

    /**
     * @param \Ibexa\ContentForms\Data\User\UserUpdateData $data
     * @param $languageCode
     */
    private function handleUserUpdateData(UserUpdateData $data, $languageCode)
    {
        foreach ($data->fieldsData as $fieldData) {
            if ('ezuser' !== $fieldData->getFieldTypeIdentifier()) {
                continue;
            }

            /** @var \Ibexa\ContentForms\Data\User\UserAccountFieldData $userAccountFieldData */
            $userAccountFieldData = $fieldData->value;
            $data->email = $userAccountFieldData->email;
            $data->password = $userAccountFieldData->password;
            $data->enabled = $userAccountFieldData->enabled;

            /** @var \Ibexa\Core\FieldType\User\Value $userValue */
            $userValue = clone $data->user->getField($fieldData->field->fieldDefIdentifier, $languageCode)->value;
            $userValue->email = $data->email;
            $userValue->enabled = $data->enabled;
            $userValue->plainPassword = $data->password;
            $fieldData->value = $userValue;

            return;
        }
    }
}

class_alias(UserFieldsSubscriber::class, 'EzSystems\EzPlatformContentForms\Form\EventSubscriber\UserFieldsSubscriber');
