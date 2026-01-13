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
    private const DOMAIN = 'example.com';
    private const MAIL = 'foo@' . self::DOMAIN;
    private const TEL = '+123456';
    private const URL_HTTP = 'http://' . self::DOMAIN;
    private const URL_HTTPS = 'https://' . self::DOMAIN;
    private const URL_MAILTO = 'mailto:' . self::MAIL;
    private const URL_RELATIVE = '/foo/bar/';
    private const URL_SFTP = 'sftp://' . self::DOMAIN;
    private const URL_TEL = 'tel:' . self::TEL;

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
            ['link' => self::DOMAIN],
            ['link' => self::URL_HTTP],
        ];

        yield 'does not modify https url' => [
            ['link' => self::URL_HTTPS],
            ['link' => self::URL_HTTPS],
        ];

        yield 'does not modify http url' => [
            ['link' => self::URL_HTTP],
            ['link' => self::URL_HTTP],
        ];

        yield 'keep relative url with leading / intact' => [
            ['link' => self::URL_RELATIVE],
            ['link' => self::URL_RELATIVE],
        ];

        yield 'keeps ftp intact' => [
            ['link' => self::URL_SFTP],
            ['link' => self::URL_SFTP],
        ];

        yield 'keeps tel intact' => [
            ['link' => self::URL_TEL],
            ['link' => self::URL_TEL],
        ];

        yield 'adds default tel' => [
            ['link' => self::TEL],
            ['link' => self::URL_TEL],
            'tel',
        ];

        yield 'keeps mailto intact' => [
            ['link' => self::URL_MAILTO],
            ['link' => self::URL_MAILTO],
        ];

        yield 'adds default mailto' => [
            ['link' => self::MAIL],
            ['link' => self::URL_MAILTO],
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
