<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Processor;

use Ibexa\ContentForms\Data\Content\ContentCreateData;
use Ibexa\ContentForms\Data\Content\ContentUpdateData;
use Ibexa\ContentForms\Data\NewnessCheckable;
use Ibexa\ContentForms\Event\ContentFormEvents;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentStruct;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Listens for and processes RepositoryForm events: publish, remove draft, save draft...
 */
final readonly class ContentFormProcessor implements EventSubscriberInterface
{
    public function __construct(
        private ContentService $contentService,
        private LocationService $locationService,
        private RouterInterface $router
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentFormEvents::CONTENT_PUBLISH => ['processPublish', 10],
            ContentFormEvents::CONTENT_PUBLISH_AND_EDIT => ['processPublishAndEdit', 10],
            ContentFormEvents::CONTENT_CANCEL => ['processCancel', 10],
            ContentFormEvents::CONTENT_SAVE_DRAFT => ['processSaveDraft', 10],
            ContentFormEvents::CONTENT_SAVE_DRAFT_AND_CLOSE => ['processSaveDraftAndClose', 10],
            ContentFormEvents::CONTENT_CREATE_DRAFT => ['processCreateDraft', 10],
        ];
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function processSaveDraft(FormActionEvent $event): void
    {
        /** @var \Ibexa\ContentForms\Data\Content\ContentCreateData|\Ibexa\ContentForms\Data\Content\ContentUpdateData $data */
        $data = $event->getData();
        $form = $event->getForm();

        $formConfig = $form->getConfig();
        $languageCode = $formConfig->getOption('languageCode');
        $draft = $this->saveDraft($data, $languageCode, []);
        $referrerLocation = $event->getOption('referrerLocation');
        $contentLocation = $this->resolveLocation($draft, $referrerLocation, $data);

        $event->setPayload('content', $draft);
        $event->setPayload('is_new', $draft->getContentInfo()->isDraft());

        $defaultUrl = $this->router->generate('ibexa.content.draft.edit', [
            'contentId' => $draft->id,
            'versionNo' => $draft->getVersionInfo()->getVersionNo(),
            'language' => $languageCode,
            'locationId' => $contentLocation?->id,
        ]);
        $event->setResponse(new RedirectResponse($formConfig->getAction() ?: $defaultUrl));
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function processSaveDraftAndClose(FormActionEvent $event): void
    {
        /** @var \Ibexa\ContentForms\Data\Content\ContentCreateData|\Ibexa\ContentForms\Data\Content\ContentUpdateData $data */
        $data = $event->getData();
        $form = $event->getForm();

        $formConfig = $form->getConfig();
        $languageCode = $formConfig->getOption('languageCode');
        $draft = $this->saveDraft($data, $languageCode, []);
        $referrerLocation = $event->getOption('referrerLocation');

        if ($referrerLocation === null) {
            $versionInfo = $data->getContentDraft()->getVersionInfo();
            $contentInfo = $versionInfo->getContentInfo();

            $currentVersion = $this->contentService->loadContentByContentInfo($contentInfo);

            if ($currentVersion->getVersionInfo()->status === VersionInfo::STATUS_PUBLISHED) {
                $publishedContentInfo = $currentVersion->getVersionInfo()->getContentInfo();
                $redirectionLocationId = $publishedContentInfo->getMainLocationId();
                $redirectionContentId = $publishedContentInfo->getId();
            } else {
                $parentLocation = $this->locationService->loadParentLocationsForDraftContent($versionInfo)[0];
                $redirectionLocationId = $parentLocation->getId();
                $redirectionContentId = $parentLocation->getContentId();
            }
        } else {
            $redirectionLocationId = $referrerLocation->id;
            $redirectionContentId = $referrerLocation->contentId;
        }

        $event->setPayload('content', $draft);
        $event->setPayload('is_new', $draft->getContentInfo()->isDraft());

        $defaultUrl = $this->router->generate(
            'ibexa.content.view',
            [
                'contentId' => $redirectionContentId,
                'locationId' => $redirectionLocationId,
            ]
        );

        $event->setResponse(new RedirectResponse($formConfig->getAction() ?: $defaultUrl));
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function processPublish(FormActionEvent $event): void
    {
        /** @var \Ibexa\ContentForms\Data\Content\ContentCreateData|\Ibexa\ContentForms\Data\Content\ContentUpdateData $data */
        $data = $event->getData();
        $form = $event->getForm();
        $referrerLocation = $event->getOption('referrerLocation');

        $draft = $this->saveDraft($data, $form->getConfig()->getOption('languageCode'));
        $versionInfo = $draft->getVersionInfo();
        $content = $this->contentService->publishVersion(
            $versionInfo,
            [$versionInfo->getInitialLanguage()->getLanguageCode()]
        );

        $event->setPayload('content', $content);
        $event->setPayload('is_new', $draft->getContentInfo()->isDraft());

        $locationId = $referrerLocation !== null && $data instanceof ContentUpdateData
            ? $referrerLocation->id
            : $content->getContentInfo()->getMainLocationId();

        $contentId = $content->getId();
        $redirectUrl = $form['redirectUrlAfterPublish']->getData() ?: $this->router->generate(
            'ibexa.content.view',
            [
                'contentId' => $contentId,
                'locationId' => $locationId,
                'publishedContentId' => $contentId,
            ]
        );

        $event->setResponse(new RedirectResponse($redirectUrl));
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function processPublishAndEdit(FormActionEvent $event): void
    {
        /** @var \Ibexa\ContentForms\Data\Content\ContentCreateData|\Ibexa\ContentForms\Data\Content\ContentUpdateData $data */
        $data = $event->getData();
        $form = $event->getForm();
        $referrerLocation = $event->getOption('referrerLocation');

        $formConfig = $form->getConfig();
        $languageCode = $formConfig->getOption('languageCode');
        $draft = $this->saveDraft($data, $languageCode);
        $versionInfo = $draft->getVersionInfo();
        $content = $this->contentService->publishVersion(
            $versionInfo,
            [
                $versionInfo->getInitialLanguage()->getLanguageCode(),
            ]
        );

        $contentInfo = $content->getContentInfo();
        $contentVersionInfo = $content->getVersionInfo();
        $newDraft = $this->contentService->createContentDraft($contentInfo, $contentVersionInfo);

        $event->setPayload('content', $newDraft);
        $event->setPayload('is_new', $newDraft->getContentInfo()->isDraft());

        $redirectUrl = $this->router->generate('ibexa.content.draft.edit', [
            'contentId' => $newDraft->getId(),
            'versionNo' => $newDraft->getVersionInfo()->getVersionNo(),
            'language' => $newDraft->getContentInfo()->getMainLanguageCode(),
            'locationId' => $referrerLocation?->getId(),
        ]);

        $event->setResponse(new RedirectResponse($redirectUrl));
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     */
    public function processCancel(FormActionEvent $event): void
    {
        /** @var \Ibexa\ContentForms\Data\Content\ContentCreateData|\Ibexa\ContentForms\Data\Content\ContentUpdateData $data */
        $data = $event->getData();

        if ($data->isNew()) {
            $parentLocation = $this->locationService->loadLocation(
                $data->getLocationStructs()[0]->parentLocationId
            );
            $response = new RedirectResponse($this->router->generate(
                'ibexa.content.view',
                [
                    'contentId' => $parentLocation->getContentId(),
                    'locationId' => $parentLocation->getId(),
                ]
            ));
            $event->setResponse($response);

            return;
        }

        $content = $data->getContentDraft();
        $contentInfo = $content->getContentInfo();
        $versionInfo = $data->getContentDraft()->getVersionInfo();

        $event->setPayload('content', $content);

        // if there is only one version you have to remove whole content instead of a version itself
        if (1 === count($this->contentService->loadVersions($contentInfo))) {
            $parentLocation = $this->locationService->loadParentLocationsForDraftContent($versionInfo)[0];
            $redirectionLocationId = $parentLocation->getId();
            $redirectionContentId = $parentLocation->getContentId();
        } else {
            $redirectionLocationId = $contentInfo->getMainLocationId();
            $redirectionContentId = $contentInfo->getId();
        }

        $this->contentService->deleteVersion($versionInfo);

        $url = $this->router->generate(
            'ibexa.content.view',
            [
                'contentId' => $redirectionContentId,
                'locationId' => $redirectionLocationId,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $event->setResponse(new RedirectResponse($url));
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function processCreateDraft(FormActionEvent $event): void
    {
        /** @var \Ibexa\ContentForms\Data\Content\CreateContentDraftData $createContentDraft */
        $createContentDraft = $event->getData();

        $contentInfo = $this->contentService->loadContentInfo($createContentDraft->contentId);
        $versionInfo = $this->contentService->loadVersionInfo($contentInfo, $createContentDraft->fromVersionNo);
        $contentDraft = $this->contentService->createContentDraft($contentInfo, $versionInfo);
        $referrerLocation = $event->getOption('referrerLocation');

        $event->setPayload('content', $contentDraft);
        $event->setPayload('is_new', $contentDraft->getContentInfo()->isDraft());

        $contentEditUrl = $this->router->generate('ibexa.content.draft.edit', [
            'contentId' => $contentDraft->getId(),
            'versionNo' => $contentDraft->getVersionInfo()->getVersionNo(),
            'language' => $contentDraft->getContentInfo()->getMainLanguageCode(),
            'locationId' => $referrerLocation?->id,
        ]);
        $event->setResponse(new RedirectResponse($contentEditUrl));
    }

    /**
     * Saves content draft corresponding to $data.
     * Depending on the nature of $data (create or update data), the draft will either be created or simply updated.
     *
     * @param string[]|null $fieldIdentifiersToValidate
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function saveDraft(
        ContentStruct|ContentCreateData|ContentUpdateData $data,
        string $languageCode,
        ?array $fieldIdentifiersToValidate = null
    ): Content {
        $mainLanguageCode = $this->resolveMainLanguageCode($data);
        foreach ($data->getFieldsData() as $fieldDefIdentifier => $fieldData) {
            if ($mainLanguageCode !== $languageCode && !$fieldData->getFieldDefinition()->isTranslatable()) {
                continue;
            }

            $data->setField($fieldDefIdentifier, $fieldData->getValue(), $languageCode);
        }

        if ($data instanceof ContentCreateData && $data->isNew()) {
            $contentDraft = $this->contentService->createContent(
                $data,
                $data->getLocationStructs(),
                $fieldIdentifiersToValidate
            );
        }

        if ($data instanceof ContentUpdateData && !$data->isNew()) {
            $contentDraft = $this->contentService->updateContent(
                $data->getContentDraft()->getVersionInfo(),
                $data,
                $fieldIdentifiersToValidate
            );
        }

        return $contentDraft;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function resolveMainLanguageCode(ContentStruct|ContentUpdateData $data): string
    {
        if (!$data instanceof ContentUpdateData && !$data instanceof ContentCreateData) {
            throw new InvalidArgumentException(
                '$data',
                'Expected ContentUpdateData or ContentCreateData'
            );
        }

        return $data->isNew()
            ? $data->mainLanguageCode
            : $data->contentDraft->getVersionInfo()->getContentInfo()->getMainLanguageCode();
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function resolveLocation(Content $content, ?Location $referrerLocation, NewnessCheckable $data): ?Location
    {
        $contentInfo = $content->getContentInfo();
        if ($data->isNew() || (!$contentInfo->isPublished() && null === $contentInfo->getMainLocationId())) {
            return null; // no location exists until new content is published
        }

        $mainLocationId = $contentInfo->getMainLocationId();
        if ($mainLocationId === null) {
            return null;
        }

        return $referrerLocation ?? $this->locationService->loadLocation($mainLocationId);
    }
}
