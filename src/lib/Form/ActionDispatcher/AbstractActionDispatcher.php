<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\ActionDispatcher;

use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base class for action dispatchers.
 */
abstract class AbstractActionDispatcher implements ActionDispatcherInterface
{
    private ?EventDispatcherInterface $eventDispatcher = null;

    protected ?Response $response;

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function dispatchFormAction(FormInterface $form, ValueObject $data, $actionName = null, array $options = []): void
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve($options);

        // First dispatch the default action, then $actionName.
        $event = new FormActionEvent($form, $data, $actionName ?? '', $options);
        $defaultActionEventName = $this->getActionEventBaseName();
        $this->dispatchDefaultAction($defaultActionEventName, $event);
        // Action name is not set e.g. when pressing return in a text field.
        // We have already run the default action, no need to run it again in that case.
        if (!empty($actionName)) {
            $this->dispatchAction("$defaultActionEventName.$actionName", $event);
        }
        $this->response = $event->getResponse();
    }

    /**
     * Configures options to pass to the form action event.
     * Might do nothing if there are no options.
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
    }

    /**
     * Returns base for action event name. It will be used as default action event name.
     * By convention, other action event names will have the format "<actionEventBaseName>.<actionName>".
     */
    abstract protected function getActionEventBaseName(): string;

    protected function dispatchDefaultAction(?string $defaultActionEventName, FormActionEvent $event): void
    {
        $this->eventDispatcher?->dispatch($event, $defaultActionEventName);
    }

    protected function dispatchAction(?string $actionEventName, FormActionEvent $event): void
    {
        $this->eventDispatcher?->dispatch($event, $actionEventName);
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}
