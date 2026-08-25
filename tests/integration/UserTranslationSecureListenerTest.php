<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\ContentForms;

use Ibexa\ContentForms\Form\Processor\User\UserTranslationSecureListener;
use Ibexa\Tests\Integration\Core\RepositoryTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigBuilder;

final class UserTranslationSecureListenerTest extends RepositoryTestCase
{
    private const LOGIN = 'jdoe';
    private const EMAIL = 'jdoe@mail.invalid';
    private const NEW_LANGUAGE = 'ger-DE';

    public function testTranslationFlowDoesNotRemoveUserData(): void
    {
        $userService = self::getUserService();
        $contentService = self::getContentService();
        $contentTypeService = self::getContentTypeService();

        $user = $this->createUser(self::LOGIN, 'John', 'Doe');
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
        $listener = new UserTranslationSecureListener($userService, $contentService);
        $listener->onPublish(new \Ibexa\ContentForms\Event\FormActionEvent(
            $form,
            null,
            'publish',
            [],
            ['content' => $publishedContent]
        ));

        $userAfterTranslation = $userService->loadUserByLogin(self::LOGIN);
        self::assertSame(self::LOGIN, $userAfterTranslation->login);
        self::assertSame(self::EMAIL, $userAfterTranslation->email);
        self::assertTrue($userAfterTranslation->enabled);

        $contentService->deleteTranslation($publishedContent->contentInfo, self::NEW_LANGUAGE);
        $userAfterDeletion = $userService->loadUserByLogin(self::LOGIN);

        self::assertSame(self::LOGIN, $userAfterDeletion->login);
        self::assertSame(self::EMAIL, $userAfterDeletion->email);
        self::assertTrue($userAfterDeletion->enabled);
        self::assertSame($userAfterTranslation->passwordHash, $userAfterDeletion->passwordHash);

        $contentAfterDeletion = $contentService->loadContent($publishedContent->id, [$mainLanguageCode]);
        self::assertSame(
            [$mainLanguageCode],
            $contentAfterDeletion->versionInfo->languageCodes
        );
    }
}
