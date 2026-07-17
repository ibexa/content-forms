<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Form\Processor;

use Ibexa\ContentForms\Data\Content\ContentCreateData;
use Ibexa\ContentForms\Data\Content\ContentUpdateData;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\ContentForms\Form\Processor\ContentFormProcessor;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Strategy\ContentPublication\ContentPublicationResult;
use Ibexa\Contracts\Core\Repository\Strategy\ContentPublication\ContentPublicationStrategyInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

/**
 * @covers \Ibexa\ContentForms\Form\Processor\ContentFormProcessor
 */
final class ContentFormProcessorTest extends TestCase
{
    private const CONTENT_ID = 123;
    private const DRAFT_MAIN_LOCATION_ID = 42;
    private const PUBLISHED_MAIN_LOCATION_ID = 77;
    private const REFERRER_LOCATION_ID = 55;
    private const LANGUAGE_CODE = 'eng-GB';
    private const GENERATED_URL = 'generated-url';

    public function testProcessPublishPublishesVersionViaStrategy(): void
    {
        $draft = $this->createDraft(self::DRAFT_MAIN_LOCATION_ID, false);
        $versionInfo = $draft->getVersionInfo();

        $contentService = $this->createMock(ContentService::class);
        $contentService->method('updateContent')->willReturn($draft);
        $contentService
            ->expects(self::never())
            ->method('publishVersion');

        $contentPublicationStrategy = $this->createMock(ContentPublicationStrategyInterface::class);
        $contentPublicationStrategy
            ->expects(self::once())
            ->method('publishVersion')
            ->with(self::identicalTo($versionInfo), [self::LANGUAGE_CODE])
            ->willReturn(new ContentPublicationResult($this->createPublishedContent()));

        $processor = new ContentFormProcessor(
            $contentService,
            $this->createStub(LocationService::class),
            $this->createRouterStub(),
            $contentPublicationStrategy
        );

        $processor->processPublish(
            $this->createEvent($this->createUpdateData($draft), $this->createForm())
        );
    }

    /**
     * @testWith [true, true]
     *           [false, false]
     */
    public function testProcessPublishSetsPayloads(bool $isNewContent, bool $expectedIsNewPayload): void
    {
        $draft = $this->createDraft($isNewContent ? null : self::DRAFT_MAIN_LOCATION_ID, $isNewContent);
        $data = $isNewContent ? $this->createCreateData() : $this->createUpdateData($draft);
        $event = $this->createEvent($data, $this->createForm());

        $processor = $this->createProcessor($draft, new ContentPublicationResult(null));
        $processor->processPublish($event);

        self::assertFalse($event->hasPayload('content'));
        self::assertSame($draft->getContentType(), $event->getPayload('content_type'));
        self::assertSame($expectedIsNewPayload, $event->getPayload('is_new'));
    }

    /**
     * @dataProvider providerForTestProcessPublishRedirect
     *
     * @param array<string, int|null> $expectedRouteParameters
     */
    public function testProcessPublishRedirect(
        bool $publishedSynchronously,
        bool $isNewContent,
        bool $withReferrerLocation,
        array $expectedRouteParameters
    ): void {
        $draft = $this->createDraft($isNewContent ? null : self::DRAFT_MAIN_LOCATION_ID, $isNewContent);
        $data = $isNewContent ? $this->createCreateData() : $this->createUpdateData($draft);
        $publicationResult = new ContentPublicationResult(
            $publishedSynchronously ? $this->createPublishedContent() : null
        );

        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects(self::once())
            ->method('generate')
            ->with('ibexa.content.view', $expectedRouteParameters)
            ->willReturn(self::GENERATED_URL);

        $options = $withReferrerLocation
            ? ['referrerLocation' => new Location(['id' => self::REFERRER_LOCATION_ID])]
            : [];
        $event = $this->createEvent($data, $this->createForm(), $options);

        $processor = $this->createProcessor($draft, $publicationResult, $router);
        $processor->processPublish($event);

        $response = $event->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(self::GENERATED_URL, $response->getTargetUrl());
    }

    /**
     * @return iterable<string, array{bool, bool, bool, array<string, int|null>}>
     */
    public static function providerForTestProcessPublishRedirect(): iterable
    {
        yield 'sync: update with referrer location' => [
            true,
            false,
            true,
            [
                'contentId' => self::CONTENT_ID,
                'locationId' => self::REFERRER_LOCATION_ID,
                'publishedContentId' => self::CONTENT_ID,
            ],
        ];

        yield 'sync: update without referrer location' => [
            true,
            false,
            false,
            [
                'contentId' => self::CONTENT_ID,
                'locationId' => self::PUBLISHED_MAIN_LOCATION_ID,
                'publishedContentId' => self::CONTENT_ID,
            ],
        ];

        yield 'sync: new content' => [
            true,
            true,
            false,
            [
                'contentId' => self::CONTENT_ID,
                'locationId' => self::PUBLISHED_MAIN_LOCATION_ID,
                'publishedContentId' => self::CONTENT_ID,
            ],
        ];

        yield 'async: update with referrer location' => [
            false,
            false,
            true,
            [
                'contentId' => self::CONTENT_ID,
                'locationId' => self::REFERRER_LOCATION_ID,
                'publishedContentId' => self::CONTENT_ID,
            ],
        ];

        yield 'async: update without referrer location' => [
            false,
            false,
            false,
            [
                'contentId' => self::CONTENT_ID,
                'locationId' => self::DRAFT_MAIN_LOCATION_ID,
                'publishedContentId' => self::CONTENT_ID,
            ],
        ];

        yield 'async: new content without location yet' => [
            false,
            true,
            false,
            [
                'contentId' => self::CONTENT_ID,
                'locationId' => null,
                'publishedContentId' => self::CONTENT_ID,
            ],
        ];
    }

    public function testProcessPublishUsesRedirectUrlAfterPublishFormData(): void
    {
        $draft = $this->createDraft(self::DRAFT_MAIN_LOCATION_ID, false);

        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects(self::never())
            ->method('generate');

        $event = $this->createEvent(
            $this->createUpdateData($draft),
            $this->createForm('custom-redirect-url')
        );

        $processor = $this->createProcessor($draft, new ContentPublicationResult(null), $router);
        $processor->processPublish($event);

        $response = $event->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('custom-redirect-url', $response->getTargetUrl());
    }

    private function createProcessor(
        Content $draft,
        ContentPublicationResult $publicationResult,
        ?RouterInterface $router = null
    ): ContentFormProcessor {
        $contentService = $this->createStub(ContentService::class);
        $contentService->method('createContent')->willReturn($draft);
        $contentService->method('updateContent')->willReturn($draft);

        $contentPublicationStrategy = $this->createStub(ContentPublicationStrategyInterface::class);
        $contentPublicationStrategy->method('publishVersion')->willReturn($publicationResult);

        return new ContentFormProcessor(
            $contentService,
            $this->createStub(LocationService::class),
            $router ?? $this->createRouterStub(),
            $contentPublicationStrategy
        );
    }

    private function createDraft(?int $mainLocationId, bool $neverPublished): Content
    {
        $contentInfo = new ContentInfo([
            'id' => self::CONTENT_ID,
            'mainLocationId' => $mainLocationId,
            'mainLanguageCode' => self::LANGUAGE_CODE,
            'status' => $neverPublished ? ContentInfo::STATUS_DRAFT : ContentInfo::STATUS_PUBLISHED,
        ]);

        $versionInfo = $this->createStub(VersionInfo::class);
        $versionInfo->method('getInitialLanguage')->willReturn(
            new Language(['languageCode' => self::LANGUAGE_CODE])
        );
        $versionInfo->method('getContentInfo')->willReturn($contentInfo);

        $draft = $this->createStub(Content::class);
        $draft->method('getVersionInfo')->willReturn($versionInfo);
        $draft->method('getContentInfo')->willReturn($contentInfo);
        $draft->method('getContentType')->willReturn($this->createStub(ContentType::class));

        return $draft;
    }

    private function createPublishedContent(): Content
    {
        $contentInfo = new ContentInfo([
            'id' => self::CONTENT_ID,
            'mainLocationId' => self::PUBLISHED_MAIN_LOCATION_ID,
            'mainLanguageCode' => self::LANGUAGE_CODE,
            'status' => ContentInfo::STATUS_PUBLISHED,
        ]);

        $publishedContent = $this->createStub(Content::class);
        $publishedContent->method('getContentInfo')->willReturn($contentInfo);
        $publishedContent->method('getId')->willReturn(self::CONTENT_ID);

        return $publishedContent;
    }

    private function createUpdateData(Content $contentDraft): ContentUpdateData
    {
        return new ContentUpdateData([
            'contentDraft' => $contentDraft,
            'fieldsData' => [],
        ]);
    }

    private function createCreateData(): ContentCreateData
    {
        return new ContentCreateData([
            'mainLanguageCode' => self::LANGUAGE_CODE,
            'fieldsData' => [],
        ]);
    }

    private function createForm(?string $redirectUrlAfterPublish = null): FormInterface
    {
        $formConfig = $this->createStub(FormConfigInterface::class);
        $formConfig->method('getOption')->willReturn(self::LANGUAGE_CODE);

        $redirectUrlField = $this->createStub(FormInterface::class);
        $redirectUrlField->method('getData')->willReturn($redirectUrlAfterPublish);

        $form = $this->createStub(FormInterface::class);
        $form->method('getConfig')->willReturn($formConfig);
        $form->method('offsetGet')->willReturn($redirectUrlField);

        return $form;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function createEvent(
        ContentCreateData|ContentUpdateData $data,
        FormInterface $form,
        array $options = []
    ): FormActionEvent {
        return new FormActionEvent($form, $data, 'publish', $options);
    }

    private function createRouterStub(): RouterInterface
    {
        $router = $this->createStub(RouterInterface::class);
        $router->method('generate')->willReturn(self::GENERATED_URL);

        return $router;
    }
}
