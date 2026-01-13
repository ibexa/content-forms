<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Form\EventSubscriber;

use Ibexa\ContentForms\Form\EventSubscriber\FixUrlProtocolListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

final class FixUrlProtocolListenerTest extends TestCase
{
    /**
     * @dataProvider provideUrlCases
     *
     * @param array<string, string>|null $inputData
     * @param array<string, string>|null $expectedData
     * @param string $defaultProtocol
     */
    public function testUrlProtocolHandling(?array $inputData, ?array $expectedData, ?string $defaultProtocol = 'http'): void
    {
        $form = $this->createMock(FormInterface::class);
        $listener = new FixUrlProtocolListener($defaultProtocol);

        $event = new FormEvent($form, $inputData);

        $listener->onSubmit($event);

        self::assertSame($expectedData, $event->getData());
    }

    /**
     * @return iterable<string, array{
     *     0: array<string, string>|null,
     *     1: array<string, string>|null
     * }>
     */
    public static function provideUrlCases(): iterable
    {
        yield 'adds http when protocol missing' => [
            ['link' => 'example1.com'],
            ['link' => 'http://example1.com'],
        ];

        yield 'does not modify https url' => [
            ['link' => 'https://example2.com'],
            ['link' => 'https://example2.com'],
        ];

        yield 'does not modify http url' => [
            ['link' => 'http://example3.com'],
            ['link' => 'http://example3.com'],
        ];

        yield 'keep relative url with leading / intact' => [
            ['link' => '/foo/bar'],
            ['link' => '/foo/bar'],
        ];

        yield 'keeps ftp intact' => [
            ['link' => 'ftp://example4.com'],
            ['link' => 'ftp://example4.com'],
        ];

        yield 'keeps tel intact' => [
            ['link' => 'tel:+123456'],
            ['link' => 'tel:+123456'],
        ];

        yield 'adds default tel' => [
            ['link' => '+123456'],
            ['link' => 'tel:+123456'],
            'tel',
        ];

        yield 'keeps mailto intact' => [
            ['link' => 'mailto:foo@home.com'],
            ['link' => 'mailto:foo@home.com'],
        ];

        yield 'adds default mailto' => [
            ['link' => 'bar@home'],
            ['link' => 'mailto:bar@home'],
            'mailto',
        ];

        yield 'does nothing when link is empty string' => [
            ['link' => ''],
            ['link' => ''],
        ];

        yield 'does nothing when link key is missing' => [
            [],
            [],
        ];

        yield 'does nothing when data is null' => [
            null,
            null,
        ];
    }
}
