<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Processor\User;

use Ibexa\ContentForms\Event\ContentFormEvents;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Re-applies the User data after publishing a non-main-language translation.
 *
 * The "user_account" field (ezuser) is non-translatable and its form is disabled while
 * translating (see UserAccountFieldValueFormMapper::mapFieldValueForm()), so it is never
 * submitted as part of the translation's content update. Without this listener re-running
 * UserService::updateUser() for the translated language after publish, translating User
 * content throws a content field validation error / leaves the user_account field broken
 * for that language.
 *
 * It also prevents data loss on translation removal: without re-persisting the user_account
 * field for the translation's language here, ContentService::deleteTranslation() on that
 * language would delete the User itself (login, password, etc.), even though the underlying
 * content object survives.
 */
final class UserTranslationSecureListener implements EventSubscriberInterface
{
    private UserService $userService;

    private ContentService $contentService;

    public function __construct(
        UserService $userService,
        ContentService $contentService
    ) {
        $this->userService = $userService;
        $this->contentService = $contentService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentFormEvents::CONTENT_PUBLISH => ['onPublish', 5],
            ContentFormEvents::CONTENT_PUBLISH_AND_EDIT => ['onPublish', 5],
        ];
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function onPublish(FormActionEvent $event): void
    {
        $content = $event->getPayload('content');
        if (null === $content || !$this->userService->isUser($content)) {
            return;
        }

        $languageCode = $event->getForm()->getConfig()->getOption('languageCode');
        if ($languageCode === $content->contentInfo->mainLanguageCode) {
            return;
        }

        $user = $this->userService->loadUser($content->id, [$languageCode]);
        $userStruct = $this->userService->newUserUpdateStruct();
        $userStruct->contentUpdateStruct = $this->contentService->newContentUpdateStruct();
        $userStruct->contentUpdateStruct->initialLanguageCode = $languageCode;

        $this->userService->updateUser($user, $userStruct);
    }
}
