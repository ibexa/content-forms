<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Form\Processor\User;

use Ibexa\ContentForms\Data\Content\ContentUpdateData;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\ContentForms\Form\Processor\User\UserTranslationSecureListener;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\Repository\Values\User\UserUpdateStruct;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\ContentUpdateStruct;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigBuilder;

final class UserTranslationSecureListenerTest extends TestCase
{
    private function createContent(string $mainLanguageCode): Content
    {
        $contentInfo = new ContentInfo(['id' => 42, 'mainLanguageCode' => $mainLanguageCode]);
        $versionInfo = new VersionInfo(['contentInfo' => $contentInfo]);

        return new Content(['versionInfo' => $versionInfo]);
    }

    private function createForm(string $languageCode): Form
    {
        return new Form(
            (new FormConfigBuilder('form', null, new EventDispatcher(), [
                'languageCode' => $languageCode,
            ]))->getFormConfig()
        );
    }

    private function createEvent(
        string $formLanguageCode,
        string $contentMainLanguageCode
    ): FormActionEvent {
        return new FormActionEvent(
            $this->createForm($formLanguageCode),
            new ContentUpdateData(['contentDraft' => $this->createContent($contentMainLanguageCode)]),
            'publish'
        );
    }

    public function testOnPublishDoesNothingForNonUserContent(): void
    {
        $event = $this->createEvent('ger-DE', 'eng-GB');

        $userService = $this->createMock(UserService::class);
        $userService->expects($this->once())
            ->method('isUser')
            ->with($event->getData()->getContentDraft())
            ->willReturn(false);
        $userService->expects($this->never())->method('loadUser');
        $userService->expects($this->never())->method('updateUser');

        $contentService = $this->createMock(ContentService::class);
        $contentService->expects($this->never())->method('newContentUpdateStruct');

        $listener = new UserTranslationSecureListener($userService, $contentService);

        $listener->onPublish($event);
    }

    public function testOnPublishDoesNothingWhenPublishingMainLanguage(): void
    {
        $event = $this->createEvent('eng-GB', 'eng-GB');

        $userService = $this->createMock(UserService::class);
        $userService->method('isUser')->willReturn(true);
        $userService->expects($this->never())->method('loadUser');
        $userService->expects($this->never())->method('updateUser');

        $contentService = $this->createMock(ContentService::class);
        $contentService->expects($this->never())->method('newContentUpdateStruct');

        $listener = new UserTranslationSecureListener($userService, $contentService);

        $listener->onPublish($event);
    }

    public function testOnPublishUpdatesUserForTranslationWithoutContentPayload(): void
    {
        $event = $this->createEvent('pol-PL', 'eng-GB');
        self::assertFalse($event->hasPayload('content'));

        $user = $this->createMock(User::class);
        $userService = $this->createMock(UserService::class);
        $userService->method('isUser')->willReturn(true);
        $userService->expects($this->once())
            ->method('loadUser')
            ->with(42, ['pol-PL'])
            ->willReturn($user);
        $userService->method('newUserUpdateStruct')->willReturn(new UserUpdateStruct());
        $userService->expects($this->once())->method('updateUser')->with($user);

        $contentService = $this->createMock(ContentService::class);
        $contentService->method('newContentUpdateStruct')->willReturn(new ContentUpdateStruct());

        $listener = new UserTranslationSecureListener($userService, $contentService);

        $listener->onPublish($event);
    }
}
