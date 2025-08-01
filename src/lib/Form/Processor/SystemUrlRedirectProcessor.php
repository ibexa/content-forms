<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Processor;

use Ibexa\ContentForms\Event\ContentFormEvents;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

final readonly class SystemUrlRedirectProcessor implements EventSubscriberInterface
{
    public function __construct(
        private RouterInterface $router,
        private URLAliasService $urlAliasService,
        private LocationService $locationService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentFormEvents::CONTENT_PUBLISH => ['processRedirectAfterPublish', 2],
            ContentFormEvents::CONTENT_CANCEL => ['processRedirectAfterCancel', 2],
        ];
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function processRedirectAfterPublish(FormActionEvent $event): void
    {
        if ($event->getForm()['redirectUrlAfterPublish']->getData()) {
            return;
        }

        $this->resolveSystemUrlRedirect($event);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function processRedirectAfterCancel(FormActionEvent $event): void
    {
        $this->resolveSystemUrlRedirect($event);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function resolveSystemUrlRedirect(FormActionEvent $event): void
    {
        /** @var \Symfony\Component\HttpFoundation\RedirectResponse $response */
        $response = $event->getResponse();

        if (!$response instanceof RedirectResponse) {
            return;
        }

        $params = $this->router->match($response->getTargetUrl());

        if (!in_array('locationId', $params)) {
            return;
        }

        $location = $this->locationService->loadLocation($params['locationId']);

        $event->setResponse(
            new RedirectResponse($this->urlAliasService->reverseLookup($location)->path)
        );
    }
}
