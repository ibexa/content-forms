<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Processor\User;

use Ibexa\ContentForms\Event\ContentFormEvents;
use Ibexa\ContentForms\Event\FormActionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Listens for and processes User cancel events.
 */
final readonly class UserCancelFormProcessor implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentFormEvents::USER_CANCEL => ['processCancel', 10],
        ];
    }

    public function processCancel(FormActionEvent $event): void
    {
        /** @var \Ibexa\ContentForms\Data\User\UserUpdateData|\Ibexa\ContentForms\Data\User\UserCreateData $data */
        $data = $event->getData();

        $contentInfo = $data->isNew()
            ? $data->getParentGroups()[0]->getContentInfo()
            : $data->user->getContentInfo();

        $response = new RedirectResponse($this->urlGenerator->generate('ibexa.content.view', [
            'contentId' => $contentInfo->getId(),
            'locationId' => $contentInfo->getMainLocationId(),
        ]));

        $event->setResponse($response);
    }
}
