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
class UserCancelFormProcessor implements EventSubscriberInterface
{
    /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface */
    private $urlGenerator;

    /**
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            ContentFormEvents::USER_CANCEL => ['processCancel', 10],
        ];
    }

    public function processCancel(FormActionEvent $event)
    {
        /** @var \Ibexa\ContentForms\Data\User\UserUpdateData|\Ibexa\ContentForms\Data\User\UserCreateData $data */
        $data = $event->getData();

        $contentInfo = $data->isNew()
            ? $data->getParentGroups()[0]->contentInfo
            : $data->user->contentInfo;

        $response = new RedirectResponse($this->urlGenerator->generate('ibexa.content.view', [
            'contentId' => $contentInfo->id,
            'locationId' => $contentInfo->mainLocationId,
        ]));
        $event->setResponse($response);
    }
}

class_alias(UserCancelFormProcessor::class, 'EzSystems\EzPlatformContentForms\Form\Processor\User\UserCancelFormProcessor');
