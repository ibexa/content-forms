<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FixUrlProtocolListener implements EventSubscriberInterface
{
    /** @var string|null */
    private $defaultProtocol;

    /**
     * @param string|null $defaultProtocol The URL scheme to add when there is none or null to not modify the data
     */
    public function __construct(?string $defaultProtocol = 'http')
    {
        $this->defaultProtocol = $defaultProtocol;
    }

    public function onSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        if (null === $this->defaultProtocol || empty($data) || !\is_string($data)) {
            return;
        }

        $protocol = explode(':', $data)[0];
        if ($this->hasAuthority($protocol) && preg_match('~^(?:[/.]|[\w+.-]+://|[^:/?@#]++@)~', $data)) {
            return;
        }

        if (!$this->hasAuthority($protocol) && preg_match('~^(?:[/.]|[\w+.-]+:|[^:/?@#]++@)~', $data)) {
            return;
        }

        $schemaSeparator = $this->hasAuthority($this->defaultProtocol) ? '://' : ':';
        if (!preg_match('~^(?:[/.]|[\w+.-]+' . $schemaSeparator . '|[^:/?@#]++@)~', $data)) {
            $event->setData($this->defaultProtocol . $schemaSeparator . $data);
        }
    }

    private function hasAuthority(string $protocol): bool
    {
        return in_array($protocol, ['mailto', 'tel']) ? false : true;
    }

    public static function getSubscribedEvents(): array
    {
        return [FormEvents::SUBMIT => 'onSubmit'];
    }
}
