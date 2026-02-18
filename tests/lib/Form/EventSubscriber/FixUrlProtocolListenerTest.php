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
     */
    public function testUrlProtocolHandling(?string $inputData, ?string $expectedData, string $defaultProtocol = 'https'): void
    {
        $form = $this->createMock(FormInterface::class);
        $listener = new FixUrlProtocolListener($defaultProtocol);

        $event = new FormEvent($form, $inputData);

        $listener->onSubmit($event);

        self::assertSame($expectedData, $event->getData());
    }

    /**
     * @return iterable<string, array{
     *     0: string|null,
     *     1: string|null,
     *     2?: string
     * }>
     */
    public static function provideUrlCases(): iterable
    {
        return [
            'adds https when protocol missing' => [
                self::DOMAIN,
                self::URL_HTTPS,
            ],
            'does not modify https url' => [
                self::URL_HTTPS,
                self::URL_HTTPS,
            ],
            'does not modify http url' => [
                self::URL_HTTP,
                self::URL_HTTP,
            ],
            'keep relative url with leading / intact' => [
                self::URL_RELATIVE,
                self::URL_RELATIVE,
            ],
            'keeps ftp intact' => [
                self::URL_SFTP,
                self::URL_SFTP,
            ],
            'keeps tel intact' => [
                self::URL_TEL,
                self::URL_TEL,
            ],
            'adds default tel' => [
                self::TEL,
                self::URL_TEL,
                'tel',
            ],
            'keeps mailto intact' => [
                self::URL_MAILTO,
                self::URL_MAILTO,
            ],
            'adds default mailto' => [
                self::MAIL,
                self::URL_MAILTO,
                'mailto',
            ],
            'does nothing when link is empty string' => [
                '',
                '',
            ],
            'does nothing when data is null' => [
                null,
                null,
            ],
        ];
    }
}
