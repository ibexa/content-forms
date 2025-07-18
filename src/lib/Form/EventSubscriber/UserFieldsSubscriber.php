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
final readonly class UserFieldsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::SUBMIT => 'handleUserAccountField',
        ];
    }

    /**
     * Handles User Account field in create/update struct.
     *
     * Workaround to quirky ibexa_user field type, it copies user data from field Data class to general User update/create
     * struct and injects proper Value for ibexa_user field type in order to pass validation.
     */
    public function handleUserAccountField(FormEvent $event): void
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

    private function handleUserCreateData(UserCreateData $data): void
    {
        foreach ($data->getFieldsData() as $fieldData) {
            if ('ibexa_user' !== $fieldData->getFieldTypeIdentifier()) {
                continue;
            }

            /** @var \Ibexa\ContentForms\Data\User\UserAccountFieldData $userAccountFieldData */
            $userAccountFieldData = $fieldData->value;
            $data->login = $userAccountFieldData->username ?? $data->login;
            $data->email = $userAccountFieldData->email ?? $data->email;
            $data->password = $userAccountFieldData->password ?? $data->password;
            $data->enabled = $userAccountFieldData->enabled ?? $data->enabled;

            /** @var \Ibexa\Core\FieldType\User\Value $userValue */
            $userValue = clone $data->contentType->getFieldDefinition(
                $fieldData->getField()->getFieldDefinitionIdentifier()
            )->getDefaultValue();

            $userValue->login = $data->login;
            $userValue->email = $data->email;
            $userValue->enabled = $data->enabled;
            $userValue->plainPassword = $data->password;

            $fieldData->value = $userValue;

            return;
        }
    }

    private function handleUserUpdateData(UserUpdateData $data, ?string $languageCode): void
    {
        foreach ($data->getFieldsData() as $fieldData) {
            if ('ibexa_user' !== $fieldData->getFieldTypeIdentifier()) {
                continue;
            }

            /** @var \Ibexa\ContentForms\Data\User\UserAccountFieldData $userAccountFieldData */
            $userAccountFieldData = $fieldData->value;
            $data->email = $userAccountFieldData->email ?? $data->email;
            $data->password = $userAccountFieldData->password ?? $data->password;
            $data->enabled = $userAccountFieldData->enabled ?? $data->enabled;

            /** @var \Ibexa\Core\FieldType\User\Value $userValue */
            $userValue = clone $data->user->getField(
                $fieldData->getField()->getFieldDefinitionIdentifier(),
                $languageCode
            )->getValue();

            if ($data->email !== null) {
                $userValue->email = $data->email;
            }
            if ($data->enabled !== null) {
                $userValue->enabled = $data->enabled;
            }
            if ($data->password !== null) {
                $userValue->plainPassword = $data->password;
            }

            $fieldData->value = $userValue;

            return;
        }
    }
}
