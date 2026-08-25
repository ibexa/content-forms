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

class UserTranslationSecureListener implements EventSubscriberInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\UserService */
    private $userService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    public function __construct(
        UserService $userService,
        ContentService $contentService,
    ) {
        $this->userService = $userService;
        $this->contentService = $contentService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentFormEvents::CONTENT_PUBLISH => ['onPublish', 5],
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
