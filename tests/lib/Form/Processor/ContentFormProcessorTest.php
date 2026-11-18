<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Form\Processor;

use Ibexa\ContentForms\Data\Content\ContentUpdateData;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\ContentForms\Form\Processor\ContentFormProcessor;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @covers \Ibexa\ContentForms\Form\Processor\ContentFormProcessor
 */
final class ContentFormProcessorTest extends TestCase
{
    private const CONTENT_ID = 42;
    private const MAIN_LOCATION_ID = 100;
    private const REFERRER_LOCATION_ID = 101;
    private const PARENT_LOCATION_ID = 102;
    private const PARENT_CONTENT_ID = 43;

    /** @var ContentService&MockObject */
    private $contentService;

    /** @var LocationService&MockObject */
    private $locationService;

    /** @var RouterInterface&MockObject */
    private $router;

    private ContentFormProcessor $processor;

    protected function setUp(): void
    {
        $this->contentService = $this->createMock(ContentService::class);
        $this->locationService = $this->createMock(LocationService::class);
        $this->router = $this->createMock(RouterInterface::class);

        $this->processor = new ContentFormProcessor(
            $this->contentService,
            $this->locationService,
            $this->router
        );
    }

    public function testProcessCancelRedirectsToReferrerLocation(): void
    {
        $versionInfo = $this->createVersionInfo();
        $event = $this->createCancelEvent($versionInfo, $this->createReferrerLocation());

        $this->contentService
            ->expects(self::once())
            ->method('loadVersions')
            ->willReturn([$versionInfo, $this->createVersionInfo()]);

        $this->contentService
            ->expects(self::once())
            ->method('deleteVersion')
            ->with($versionInfo);

        $this->expectRedirectTo(self::CONTENT_ID, self::REFERRER_LOCATION_ID);

        $this->processor->processCancel($event);
    }

    public function testProcessCancelRedirectsToMainLocationWithoutReferrerLocation(): void
    {
        $versionInfo = $this->createVersionInfo();
        $event = $this->createCancelEvent($versionInfo, null);

        $this->contentService
            ->expects(self::once())
            ->method('loadVersions')
            ->willReturn([$versionInfo, $this->createVersionInfo()]);

        $this->contentService
            ->expects(self::once())
            ->method('deleteVersion')
            ->with($versionInfo);

        $this->expectRedirectTo(self::CONTENT_ID, self::MAIN_LOCATION_ID);

        $this->processor->processCancel($event);
    }

    public function testProcessCancelRedirectsToParentLocationForSingleVersionContent(): void
    {
        $versionInfo = $this->createVersionInfo();
        $event = $this->createCancelEvent($versionInfo, $this->createReferrerLocation());

        $this->contentService
            ->expects(self::once())
            ->method('loadVersions')
            ->willReturn([$versionInfo]);

        $this->contentService
            ->expects(self::once())
            ->method('deleteVersion')
            ->with($versionInfo);

        $this->locationService
            ->expects(self::once())
            ->method('loadParentLocationsForDraftContent')
            ->with($versionInfo)
            ->willReturn([
                new Location([
                    'id' => self::PARENT_LOCATION_ID,
                    'contentInfo' => new ContentInfo(['id' => self::PARENT_CONTENT_ID]),
                ]),
            ]);

        $this->expectRedirectTo(self::PARENT_CONTENT_ID, self::PARENT_LOCATION_ID);

        $this->processor->processCancel($event);
    }

    private function createVersionInfo(): VersionInfo
    {
        return new VersionInfo([
            'contentInfo' => new ContentInfo([
                'id' => self::CONTENT_ID,
                'mainLocationId' => self::MAIN_LOCATION_ID,
            ]),
        ]);
    }

    private function createReferrerLocation(): Location
    {
        return new Location([
            'id' => self::REFERRER_LOCATION_ID,
            'contentInfo' => new ContentInfo(['id' => self::CONTENT_ID]),
        ]);
    }

    private function createCancelEvent(
        VersionInfo $versionInfo,
        ?Location $referrerLocation
    ): FormActionEvent {
        $data = new ContentUpdateData(['contentDraft' => new Content(['versionInfo' => $versionInfo])]);

        return new FormActionEvent(
            $this->createStub(FormInterface::class),
            $data,
            'cancel',
            ['referrerLocation' => $referrerLocation]
        );
    }

    private function expectRedirectTo(
        int $contentId,
        int $locationId
    ): void {
        $this->router
            ->expects(self::once())
            ->method('generate')
            ->with(
                'ibexa.content.view',
                [
                    'contentId' => $contentId,
                    'locationId' => $locationId,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('/redirect-url');
    }
}
