<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Processor;

use Ibexa\ContentForms\Event\ContentFormEvents;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class SystemUrlRedirectProcessor implements EventSubscriberInterface
{
    /** @var RouterInterface */
    private $router;

    /** @var URLAliasService */
    private $urlAliasService;

    /** @var LocationService */
    private $locationService;

    /**
     * @param RouterInterface $router
     * @param URLAliasService $urlAliasService
     * @param LocationService $locationService
     * @param array $siteaccessGroups
     */
    public function __construct(
        RouterInterface $router,
        URLAliasService $urlAliasService,
        LocationService $locationService
    ) {
        $this->router = $router;
        $this->urlAliasService = $urlAliasService;
        $this->locationService = $locationService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ContentFormEvents::CONTENT_PUBLISH => ['processRedirectAfterPublish', 2],
            ContentFormEvents::CONTENT_CANCEL => ['processRedirectAfterCancel', 2],
        ];
    }

    /**
     * @param FormActionEvent $event
     *
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function processRedirectAfterPublish(FormActionEvent $event): void
    {
        if ($event->getForm()['redirectUrlAfterPublish']->getData()) {
            return;
        }

        $this->resolveSystemUrlRedirect($event);
    }

    /**
     * @param FormActionEvent $event
     *
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function processRedirectAfterCancel(FormActionEvent $event): void
    {
        $this->resolveSystemUrlRedirect($event);
    }

    /**
     * @param FormActionEvent $event
     *
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    private function resolveSystemUrlRedirect(FormActionEvent $event): void
    {
        /** @var RedirectResponse $response */
        $response = $event->getResponse();

        if (!$response instanceof RedirectResponse) {
            return;
        }

        $params = $this->router->match($response->getTargetUrl());

        if (!in_array('locationId', $params)) {
            return;
        }

        $location = $this->locationService->loadLocation($params['locationId']);

        $event->setResponse(new RedirectResponse($this->urlAliasService->reverseLookup($location)->path));
    }
}

class_alias(SystemUrlRedirectProcessor::class, 'EzSystems\EzPlatformContentForms\Form\Processor\SystemUrlRedirectProcessor');
