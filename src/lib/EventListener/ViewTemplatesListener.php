<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\EventListener;

use Ibexa\ContentForms\Content\View\ContentCreateDraftView;
use Ibexa\ContentForms\User;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use Ibexa\ContentForms\User\View\UserCreateView;
use Ibexa\ContentForms\User\View\UserUpdateView;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sets the templates used by the user controller.
 */
class ViewTemplatesListener implements EventSubscriberInterface
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public static function getSubscribedEvents()
    {
        return [MVCEvents::PRE_CONTENT_VIEW => 'setViewTemplates'];
    }

    /**
     * If the event's view has a defined template, sets the view's template identifier,
     * and the 'pagelayout' parameter.
     */
    public function setViewTemplates(PreContentViewEvent $event): void
    {
        $view = $event->getContentView();
        $pagelayout = $this->configResolver->getParameter('pagelayout');

        foreach ($this->getTemplatesMap() as $viewClass => $template) {
            if ($view instanceof $viewClass) {
                $view->setTemplateIdentifier($template);
                $view->addParameters(['pagelayout' => $pagelayout]);
                $view->addParameters(['page_layout' => $pagelayout]);
            }
        }
    }

    /**
     * @return string[]
     */
    private function getTemplatesMap(): array
    {
        return [
            UserCreateView::class => $this->configResolver->getParameter('user_edit.templates.create'),
            UserUpdateView::class => $this->configResolver->getParameter('user_edit.templates.update'),
            ContentCreateDraftView::class => $this->configResolver->getParameter('content_edit.templates.create_draft'),
        ];
    }
}

class_alias(ViewTemplatesListener::class, 'EzSystems\EzPlatformContentForms\EventListener\ViewTemplatesListener');
