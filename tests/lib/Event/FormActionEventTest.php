<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Event;

use Ibexa\ContentForms\Event\FormActionEvent;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

final class FormActionEventTest extends TestCase
{
    public function testConstruct(): void
    {
        $form = $this->createMock(FormInterface::class);
        $data = new stdClass();
        $clickedButton = 'fooButton';
        $options = ['languageCode' => 'eng-GB', 'foo' => 'bar'];

        $event = new FormActionEvent($form, $data, $clickedButton, $options);
        self::assertSame($form, $event->getForm());
        self::assertSame($data, $event->getData());
        self::assertSame($clickedButton, $event->getClickedButton());
        self::assertSame($options, $event->getOptions());
    }

    public function testEventDoesntHaveResponse(): void
    {
        $event = new FormActionEvent(
            $this->createMock(FormInterface::class),
            new stdClass(),
            'fooButton'
        );
        self::assertFalse($event->hasResponse());
        self::assertNull($event->getResponse());
    }

    public function testEventSetResponse(): void
    {
        $event = new FormActionEvent(
            $this->createMock(FormInterface::class),
            new stdClass(),
            'fooButton'
        );
        self::assertFalse($event->hasResponse());
        self::assertNull($event->getResponse());

        $response = new Response();
        $event->setResponse($response);
        self::assertTrue($event->hasResponse());
        self::assertSame($response, $event->getResponse());
    }

    public function testGetOption(): void
    {
        $objectOption = new stdClass();
        $options = ['languageCode' => 'eng-GB', 'foo' => 'bar', 'obj' => $objectOption];

        $event = new FormActionEvent(
            $this->createMock(FormInterface::class),
            new stdClass(),
            'fooButton',
            $options
        );
        self::assertTrue($event->hasOption('languageCode'));
        self::assertTrue($event->hasOption('foo'));
        self::assertTrue($event->hasOption('obj'));
        self::assertSame('eng-GB', $event->getOption('languageCode'));
        self::assertSame('bar', $event->getOption('foo'));
        self::assertSame($objectOption, $event->getOption('obj'));
    }
}
