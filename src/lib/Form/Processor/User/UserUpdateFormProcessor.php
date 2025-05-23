<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Processor\User;

use Ibexa\ContentForms\Data\User\UserUpdateData;
use Ibexa\ContentForms\Event\ContentFormEvents;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Listens for and processes User update events.
 */
class UserUpdateFormProcessor implements EventSubscriberInterface
{
    private UserService $userService;

    private ContentService $contentService;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        UserService $userService,
        ContentService $contentService,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->userService = $userService;
        $this->contentService = $contentService;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentFormEvents::USER_UPDATE => ['processUpdate', 20],
        ];
    }

    public function processUpdate(FormActionEvent $event): void
    {
        $data = $event->getData();

        if (!$data instanceof UserUpdateData) {
            return;
        }

        $form = $event->getForm();
        $languageCode = $form->getConfig()->getOption('languageCode');

        $this->setContentFields($data, $languageCode);
        $user = $this->userService->updateUser($data->user, $data);

        $redirectUrl = $form['redirectUrlAfterPublish']->getData() ?: $this->urlGenerator->generate(
            'ibexa.content.view',
            [
                'contentId' => $user->id,
                'locationId' => $user->contentInfo->mainLocationId,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $event->setResponse(new RedirectResponse($redirectUrl));
    }

    /**
     * @param \Ibexa\ContentForms\Data\User\UserUpdateData $data
     * @param string $languageCode
     */
    private function setContentFields(UserUpdateData $data, string $languageCode): void
    {
        $data->contentUpdateStruct = $this->contentService->newContentUpdateStruct();

        foreach ($data->fieldsData as $fieldDefIdentifier => $fieldData) {
            $data->contentUpdateStruct->setField($fieldDefIdentifier, $fieldData->value, $languageCode);
        }
    }
}
