<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\ContentForms;

use Ibexa\ContentForms\Event\ContentFormEvents;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\ContentForms\Form\Processor\User\UserTranslationSecureListener;
use Ibexa\Tests\Integration\Core\RepositoryTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigBuilder;

final class UserTranslationSecureListenerTest extends RepositoryTestCase
{
    private const NEW_LANGUAGE = 'ger-DE';

    /**
     * @dataProvider providePublishEventNames
     */
    public function testTranslationFlowDoesNotRemoveUserData(string $eventName, string $loginSuffix): void
    {
        $login = 'jdoe_' . $loginSuffix;
        $email = $login . '@mail.invalid';

        $ibexaTestCore = $this->getIbexaTestCore();
        $userService = $ibexaTestCore->getUserService();
        $contentService = $ibexaTestCore->getContentService();
        $contentTypeService = $ibexaTestCore->getContentTypeService();

        $user = $this->createUser($login, 'John', 'Doe');
        $mainLanguageCode = $user->contentInfo->mainLanguageCode;

        $userContentType = $contentTypeService->loadContentTypeByIdentifier('user');
        $translatableFieldDefinitions = [];
        foreach ($userContentType->fieldDefinitions as $fieldDefinition) {
            if (
                $fieldDefinition->identifier !== 'user_account'
                && $fieldDefinition->isTranslatable
                && \in_array($fieldDefinition->fieldTypeIdentifier, ['ezstring', 'eztext'], true)
            ) {
                $translatableFieldDefinitions[] = $fieldDefinition;
            }
        }
        self::assertNotEmpty($translatableFieldDefinitions, 'Expected the "user" content type to have translatable fields besides "user_account".');

        $draft = $contentService->createContentDraft($user->contentInfo);
        $updateStruct = $contentService->newContentUpdateStruct();
        $updateStruct->initialLanguageCode = self::NEW_LANGUAGE;
        foreach ($translatableFieldDefinitions as $fieldDefinition) {
            $updateStruct->setField($fieldDefinition->identifier, 'German value', self::NEW_LANGUAGE);
        }
        $updatedDraft = $contentService->updateContent($draft->versionInfo, $updateStruct);
        $publishedContent = $contentService->publishVersion($updatedDraft->versionInfo);

        $form = new Form(
            (new FormConfigBuilder('form', null, new EventDispatcher(), [
                'languageCode' => self::NEW_LANGUAGE,
            ]))->getFormConfig()
        );

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new UserTranslationSecureListener($userService, $contentService));
        $eventDispatcher->dispatch(
            new FormActionEvent(
                $form,
                null,
                'publish',
                [],
                ['content' => $publishedContent]
            ),
            $eventName
        );

        $userAfterTranslation = $userService->loadUserByLogin($login);
        self::assertSame($login, $userAfterTranslation->login);
        self::assertSame($email, $userAfterTranslation->email);
        self::assertTrue($userAfterTranslation->enabled);

        $contentService->deleteTranslation($publishedContent->contentInfo, self::NEW_LANGUAGE);
        $userAfterDeletion = $userService->loadUserByLogin($login);

        self::assertSame($login, $userAfterDeletion->login);
        self::assertSame($email, $userAfterDeletion->email);
        self::assertTrue($userAfterDeletion->enabled);
        self::assertSame($userAfterTranslation->passwordHash, $userAfterDeletion->passwordHash);

        $contentAfterDeletion = $contentService->loadContent($publishedContent->id, [$mainLanguageCode]);
        self::assertSame(
            [$mainLanguageCode],
            $contentAfterDeletion->versionInfo->languageCodes
        );
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function providePublishEventNames(): iterable
    {
        return [
            'publish' => [ContentFormEvents::CONTENT_PUBLISH, 'publish'],
            'publish and edit' => [ContentFormEvents::CONTENT_PUBLISH_AND_EDIT, 'publish_and_edit'],
        ];
    }
}
