<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\EventListener\FixUrlProtocolListener as BaseFixUrlProtocolListener;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FixUrlProtocolListener implements EventSubscriberInterface
{
    /** @var string|null */
    private $defaultProtocol;

    /** @var \Symfony\Component\Form\Extension\Core\EventListener\FixUrlProtocolListener */
    private $fixUrlProtocolListener;

    /**
     * @param string|null $defaultProtocol The URL scheme to add when there is none or null to not modify the data
     */
    public function __construct(?string $defaultProtocol = 'http')
    {
        $this->defaultProtocol = $defaultProtocol;
        $this->fixUrlProtocolListener = new BaseFixUrlProtocolListener($defaultProtocol);
    }

    public function onSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $dataLink = $data['link'] ?? null;
        if (null === $this->defaultProtocol || empty($data) || empty($dataLink) || !\is_string($dataLink)) {
            return;
        }

        $protocol = explode(':', $dataLink)[0];
        if ($this->hasAuthority($protocol) && $this->hasAuthority($this->defaultProtocol)) {
            $event->setData($dataLink);
            $this->fixUrlProtocolListener->onSubmit($event);
            $data['link'] = $event->getData();
            $event->setData($data);

            return;
        }

        if (!$this->hasAuthority($protocol) && preg_match('~^(?:[/.]|[\w+.-]+:|[^:/?@#]++@)~', $dataLink)) {
            return;
        }

        if ($this->hasAuthority($this->defaultProtocol)) {
            $schemaSeparator = '://';
            $regExp = '~^(?:[/.]|[\w+.-]+//|[^:/?@#]++@)~';
        } else {
            $schemaSeparator = ':';
            $regExp = '~^[\w+.-]+:~'; // allowing emails for non-http/https/file
        }

        if (!preg_match($regExp, $dataLink)) {
            $data['link'] = $this->defaultProtocol . $schemaSeparator . $dataLink;
            $event->setData($data);
        }
    }

    private function hasAuthority(string $protocol): bool
    {
        return !in_array($protocol, ['mailto', 'tel']);
    }

    public static function getSubscribedEvents(): array
    {
        return [FormEvents::SUBMIT => 'onSubmit'];
    }
}
