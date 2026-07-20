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
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
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
    private const TREE_ROOT_LOCATION_ID = 2;
    private const LANGUAGE_CODE = 'eng-GB';
    private const GENERATED_URL = 'generated-url';

    /**
     * @dataProvider provideProcessPublishCases
     *
     * @param array<string, int|null>|null $expectedRouteParameters route parameters the redirect
     *        is expected to be generated with, or null when a custom redirect URL is provided and
     *        the router must not be invoked at all
     */
    public function testProcessPublish(
        bool $isNewContent,
        bool $publishedSynchronously,
        bool $withReferrerLocation,
        ?string $customRedirectUrl,
        ?array $expectedRouteParameters
    ): void {
        $draft = $this->createDraft(
            $isNewContent ? null : self::DRAFT_MAIN_LOCATION_ID,
            $isNewContent ? ContentInfo::STATUS_DRAFT : ContentInfo::STATUS_PUBLISHED,
        );
        $versionInfo = $draft->getVersionInfo();

        $data = $isNewContent ? $this->createCreateData() : $this->createUpdateData($draft);
        $options = $withReferrerLocation
            ? ['referrerLocation' => new Location(['id' => self::REFERRER_LOCATION_ID])]
            : [];
        $event = $this->createEvent($data, $this->createForm($customRedirectUrl), $options);

        // The draft is persisted via create/update; publication must never go through the service.
        $contentService = $this->createMock(ContentService::class);
        $contentService->method('createContent')->willReturn($draft);
        $contentService->method('updateContent')->willReturn($draft);
        $contentService
            ->expects(self::never())
            ->method('publishVersion');

        // Publication is delegated to the strategy, called once with the draft version and language.
        $publishedContent = $publishedSynchronously ? $this->createPublishedContent() : null;
        $contentPublicationStrategy = $this->createMock(ContentPublicationStrategyInterface::class);
        $contentPublicationStrategy
            ->expects(self::once())
            ->method('publishVersion')
            ->with(self::identicalTo($versionInfo), [self::LANGUAGE_CODE])
            ->willReturn(new ContentPublicationResult($publishedContent));

        // The router is called once with the expected route parameters, unless a custom redirect
        // URL coming from the form data short-circuits URL generation entirely.
        $router = $this->createMock(RouterInterface::class);
        if ($expectedRouteParameters === null) {
            $router
                ->expects(self::never())
                ->method('generate');
        } else {
            $router
                ->expects(self::once())
                ->method('generate')
                ->with('ibexa.content.view', $expectedRouteParameters)
                ->willReturn(self::GENERATED_URL);
        }

        // The deferred (async) redirect resolves its location from the content tree root config
        // and the location lookup.
        $configResolver = $this->createStub(ConfigResolverInterface::class);
        $configResolver->method('getParameter')->willReturn(self::TREE_ROOT_LOCATION_ID);

        $locationService = $this->createStub(LocationService::class);
        $locationService->method('loadLocation')->willReturn(
            new Location([
                'id' => self::TREE_ROOT_LOCATION_ID,
                'contentInfo' => new ContentInfo(['id' => self::CONTENT_ID]),
            ])
        );

        $processor = new ContentFormProcessor(
            $contentService,
            $locationService,
            $router,
            $configResolver,
            $contentPublicationStrategy
        );

        $processor->processPublish($event);

        self::assertSame($draft->getContentType(), $event->getPayload('content_type'));
        self::assertSame($isNewContent, $event->getPayload('is_new'));

        $response = $event->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame($customRedirectUrl ?? self::GENERATED_URL, $response->getTargetUrl());
    }

    /**
     * @return iterable<array{
     *     isNewContent: bool,
     *     publishedSynchronously: bool,
     *     withReferrerLocation: bool,
     *     customRedirectUrl: string|null,
     *     expectedRouteParameters: array{
     *         contentId: int,
     *         locationId: int,
     *         publishedContentId: int
     *     }|null
     * }>
     */
    public static function provideProcessPublishCases(): iterable
    {
        yield 'sync: update with referrer location' => [
            'isNewContent' => false,
            'publishedSynchronously' => true,
            'withReferrerLocation' => true,
            'customRedirectUrl' => null,
            'expectedRouteParameters' => [
                'contentId' => self::CONTENT_ID,
                'locationId' => self::REFERRER_LOCATION_ID,
                'publishedContentId' => self::CONTENT_ID,
            ],
        ];

        yield 'sync: update without referrer location' => [
            'isNewContent' => false,
            'publishedSynchronously' => true,
            'withReferrerLocation' => false,
            'customRedirectUrl' => null,
            'expectedRouteParameters' => [
                'contentId' => self::CONTENT_ID,
                'locationId' => self::PUBLISHED_MAIN_LOCATION_ID,
                'publishedContentId' => self::CONTENT_ID,
            ],
        ];

        yield 'sync: new content' => [
            'isNewContent' => true,
            'publishedSynchronously' => true,
            'withReferrerLocation' => false,
            'customRedirectUrl' => null,
            'expectedRouteParameters' => [
                'contentId' => self::CONTENT_ID,
                'locationId' => self::PUBLISHED_MAIN_LOCATION_ID,
                'publishedContentId' => self::CONTENT_ID,
            ],
        ];

        yield 'async: update with referrer location' => [
            'isNewContent' => false,
            'publishedSynchronously' => false,
            'withReferrerLocation' => true,
            'customRedirectUrl' => null,
            'expectedRouteParameters' => [
                'contentId' => self::CONTENT_ID,
                'locationId' => self::REFERRER_LOCATION_ID,
                'publishedContentId' => self::CONTENT_ID,
            ],
        ];

        yield 'async: update without referrer location redirects to tree root' => [
            'isNewContent' => false,
            'publishedSynchronously' => false,
            'withReferrerLocation' => false,
            'customRedirectUrl' => null,
            'expectedRouteParameters' => [
                'contentId' => self::CONTENT_ID,
                'locationId' => self::TREE_ROOT_LOCATION_ID,
                'publishedContentId' => self::CONTENT_ID,
            ],
        ];

        yield 'async: new content without location yet redirects to tree root' => [
            'isNewContent' => true,
            'publishedSynchronously' => false,
            'withReferrerLocation' => false,
            'customRedirectUrl' => null,
            'expectedRouteParameters' => [
                'contentId' => self::CONTENT_ID,
                'locationId' => self::TREE_ROOT_LOCATION_ID,
                'publishedContentId' => self::CONTENT_ID,
            ],
        ];

        yield 'custom redirect URL after publish bypasses router' => [
            'isNewContent' => false,
            'publishedSynchronously' => false,
            'withReferrerLocation' => false,
            'customRedirectUrl' => 'custom-redirect-url',
            'expectedRouteParameters' => null,
        ];
    }

    private function createDraft(?int $mainLocationId, int $status): Content
    {
        $contentInfo = new ContentInfo([
            'id' => self::CONTENT_ID,
            'mainLocationId' => $mainLocationId,
            'mainLanguageCode' => self::LANGUAGE_CODE,
            'status' => $status,
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

    /**
     * @return \Symfony\Component\Form\FormInterface<mixed>
     */
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
     * @param \Symfony\Component\Form\FormInterface<mixed> $form
     * @param array<string, mixed> $options
     */
    private function createEvent(
        ContentCreateData|ContentUpdateData $data,
        FormInterface $form,
        array $options = []
    ): FormActionEvent {
        return new FormActionEvent($form, $data, 'publish', $options);
    }
}
