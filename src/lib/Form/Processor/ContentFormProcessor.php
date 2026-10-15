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
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\URLAliasService;
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
class ContentFormProcessor implements EventSubscriberInterface
{
    /** @var ContentService */
    private $contentService;

    /** @var LocationService */
    private $locationService;

    /** @var RouterInterface */
    private $router;

    /**
     * @param ContentService $contentService
     * @param LocationService $locationService
     * @param RouterInterface $router
     * @param URLAliasService $urlAliasService
     */
    public function __construct(
        ContentService $contentService,
        LocationService $locationService,
        RouterInterface $router
    ) {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->router = $router;
    }

    /**
     * @return array
     */
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
     * @param FormActionEvent $event
     *
     * @throws BadStateException
     * @throws ContentFieldValidationException
     * @throws ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws InvalidArgumentException
     */
    public function processSaveDraft(FormActionEvent $event)
    {
        /** @var ContentCreateData|ContentUpdateData $data */
        $data = $event->getData();
        $form = $event->getForm();

        $formConfig = $form->getConfig();
        $languageCode = $formConfig->getOption('languageCode');
        $draft = $this->saveDraft($data, $languageCode, []);
        $referrerLocation = $event->getOption('referrerLocation');
        $contentLocation = $this->resolveLocation($draft, $referrerLocation, $data);

        $event->setPayload('content', $draft);
        $event->setPayload('is_new', $draft->contentInfo->isDraft());

        $defaultUrl = $this->router->generate('ibexa.content.draft.edit', [
            'contentId' => $draft->id,
            'versionNo' => $draft->getVersionInfo()->versionNo,
            'language' => $languageCode,
            'locationId' => null !== $contentLocation ? $contentLocation->id : null,
        ]);
        $event->setResponse(new RedirectResponse($formConfig->getAction() ?: $defaultUrl));
    }

    /**
     * @throws BadStateException
     * @throws ContentFieldValidationException
     * @throws ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws InvalidArgumentException
     */
    public function processSaveDraftAndClose(FormActionEvent $event): void
    {
        /** @var ContentCreateData|ContentUpdateData $data */
        $data = $event->getData();
        $form = $event->getForm();

        $formConfig = $form->getConfig();
        $languageCode = $formConfig->getOption('languageCode');
        $draft = $this->saveDraft($data, $languageCode, []);
        $referrerLocation = $event->getOption('referrerLocation');

        if ($referrerLocation === null) {
            $versionInfo = $data->contentDraft->getVersionInfo();
            $contentInfo = $versionInfo->getContentInfo();

            $currentVersion = $this->contentService->loadContentByContentInfo($contentInfo);

            if ($currentVersion->getVersionInfo()->status === VersionInfo::STATUS_PUBLISHED) {
                $publishedContentInfo = $currentVersion->getVersionInfo()->getContentInfo();
                $redirectionLocationId = $publishedContentInfo->mainLocationId;
                $redirectionContentId = $publishedContentInfo->getId();
            } else {
                $parentLocation = $this->locationService->loadParentLocationsForDraftContent($versionInfo)[0];
                $redirectionLocationId = $parentLocation->id;
                $redirectionContentId = $parentLocation->contentId;
            }
        } else {
            $redirectionLocationId = $referrerLocation->id;
            $redirectionContentId = $referrerLocation->contentId;
        }

        $event->setPayload('content', $draft);
        $event->setPayload('is_new', $draft->contentInfo->isDraft());

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
     * @param FormActionEvent $event
     *
     * @throws BadStateException
     * @throws ContentFieldValidationException
     * @throws ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws UnauthorizedException
     * @throws InvalidArgumentException
     */
    public function processPublish(FormActionEvent $event)
    {
        /** @var ContentCreateData|ContentUpdateData $data */
        $data = $event->getData();
        $form = $event->getForm();
        $referrerLocation = $event->getOption('referrerLocation');

        $draft = $this->saveDraft($data, $form->getConfig()->getOption('languageCode'));
        $versionInfo = $draft->versionInfo;
        $content = $this->contentService->publishVersion($versionInfo, [$versionInfo->initialLanguageCode]);

        $event->setPayload('content', $content);
        $event->setPayload('is_new', $draft->contentInfo->isDraft());

        $locationId = $referrerLocation !== null && $data instanceof ContentUpdateData
            ? $referrerLocation->id
            : $content->contentInfo->mainLocationId;

        $contentId = $content->id;
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
     * @throws BadStateException
     * @throws ContentFieldValidationException
     * @throws ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws UnauthorizedException
     * @throws InvalidArgumentException
     */
    public function processPublishAndEdit(FormActionEvent $event)
    {
        /** @var ContentCreateData|ContentUpdateData $data */
        $data = $event->getData();
        $form = $event->getForm();
        $referrerLocation = $event->getOption('referrerLocation');

        $formConfig = $form->getConfig();
        $languageCode = $formConfig->getOption('languageCode');
        $draft = $this->saveDraft($data, $languageCode);
        $versionInfo = $draft->versionInfo;
        $content = $this->contentService->publishVersion($versionInfo, [$versionInfo->initialLanguageCode]);

        $contentInfo = $content->contentInfo;
        $contentVersionInfo = $content->getVersionInfo();
        $newDraft = $this->contentService->createContentDraft($contentInfo, $contentVersionInfo);

        $event->setPayload('content', $newDraft);
        $event->setPayload('is_new', $newDraft->contentInfo->isDraft());

        $redirectUrl = $this->router->generate('ibexa.content.draft.edit', [
            'contentId' => $newDraft->id,
            'versionNo' => $newDraft->getVersionInfo()->versionNo,
            'language' => $newDraft->contentInfo->mainLanguageCode,
            'locationId' => null !== $referrerLocation ? $referrerLocation->id : null,
        ]);

        $event->setResponse(new RedirectResponse($redirectUrl));
    }

    /**
     * @param FormActionEvent $event
     *
     * @throws BadStateException
     * @throws UnauthorizedException
     */
    public function processCancel(FormActionEvent $event)
    {
        /** @var ContentCreateData|ContentUpdateData $data */
        $data = $event->getData();

        if ($data->isNew()) {
            $parentLocation = $this->locationService->loadLocation($data->getLocationStructs()[0]->parentLocationId);
            $response = new RedirectResponse($this->router->generate(
                'ibexa.content.view',
                [
                    'contentId' => $parentLocation->contentId,
                    'locationId' => $parentLocation->id,
                ]
            ));
            $event->setResponse($response);

            return;
        }

        $content = $data->contentDraft;
        $contentInfo = $content->contentInfo;
        $versionInfo = $data->contentDraft->getVersionInfo();

        $event->setPayload('content', $content);

        // if there is only one version you have to remove whole content instead of a version itself
        if (1 === count($this->contentService->loadVersions($contentInfo))) {
            $parentLocation = $this->locationService->loadParentLocationsForDraftContent($versionInfo)[0];
            $redirectionLocationId = $parentLocation->id;
            $redirectionContentId = $parentLocation->contentId;
        } else {
            $redirectionLocationId = $contentInfo->mainLocationId;
            $redirectionContentId = $contentInfo->id;
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
     * @param FormActionEvent $event
     *
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function processCreateDraft(FormActionEvent $event)
    {
        /** @var $createContentDraft \Ibexa\ContentForms\Data\Content\CreateContentDraftData */
        $createContentDraft = $event->getData();

        $contentInfo = $this->contentService->loadContentInfo((int)$createContentDraft->contentId);
        $versionInfo = $this->contentService->loadVersionInfo($contentInfo, (int)$createContentDraft->fromVersionNo);
        $contentDraft = $this->contentService->createContentDraft($contentInfo, $versionInfo);
        $referrerLocation = $event->getOption('referrerLocation');

        $event->setPayload('content', $contentDraft);
        $event->setPayload('is_new', $contentDraft->contentInfo->isDraft());

        $contentEditUrl = $this->router->generate('ibexa.content.draft.edit', [
            'contentId' => $contentDraft->id,
            'versionNo' => $contentDraft->getVersionInfo()->versionNo,
            'language' => $contentDraft->contentInfo->mainLanguageCode,
            'locationId' => null !== $referrerLocation ? $referrerLocation->id : null,
        ]);
        $event->setResponse(new RedirectResponse($contentEditUrl));
    }

    /**
     * Saves content draft corresponding to $data.
     * Depending on the nature of $data (create or update data), the draft will either be created or simply updated.
     *
     * @param ContentStruct|ContentCreateData|ContentUpdateData $data
     * @param $languageCode
     *
     * @return Content
     *
     * @throws BadStateException
     * @throws ContentFieldValidationException
     * @throws ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws UnauthorizedException
     * @throws InvalidArgumentException
     */
    private function saveDraft(
        ContentStruct $data,
        string $languageCode,
        ?array $fieldIdentifiersToValidate = null
    ) {
        $mainLanguageCode = $this->resolveMainLanguageCode($data);
        foreach ($data->fieldsData as $fieldDefIdentifier => $fieldData) {
            if ($mainLanguageCode != $languageCode && !$fieldData->fieldDefinition->isTranslatable) {
                continue;
            }

            $data->setField($fieldDefIdentifier, $fieldData->value, $languageCode);
        }

        if ($data->isNew()) {
            $contentDraft = $this->contentService->createContent($data, $data->getLocationStructs(), $fieldIdentifiersToValidate);
        } else {
            $contentDraft = $this->contentService->updateContent($data->contentDraft->getVersionInfo(), $data, $fieldIdentifiersToValidate);
        }

        return $contentDraft;
    }

    /**
     * @param ContentCreateData|ContentUpdateData $data
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    private function resolveMainLanguageCode($data): string
    {
        if (!$data instanceof ContentUpdateData && !$data instanceof ContentCreateData) {
            throw new InvalidArgumentException(
                '$data',
                'Expected ContentUpdateData or ContentCreateData'
            );
        }

        return $data->isNew()
            ? $data->mainLanguageCode
            : $data->contentDraft->getVersionInfo()->getContentInfo()->mainLanguageCode;
    }

    /**
     * @param Content $content
     * @param Location|null $referrerLocation
     * @param NewnessCheckable $data
     *
     * @return Location|null
     *
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    private function resolveLocation(
        Content $content,
        ?Location $referrerLocation,
        NewnessCheckable $data
    ): ?Location {
        if ($data->isNew() || (!$content->contentInfo->published && null === $content->contentInfo->mainLocationId)) {
            return null; // no location exists until new content is published
        }

        return $referrerLocation ?? $this->locationService->loadLocation($content->contentInfo->mainLocationId);
    }
}

class_alias(ContentFormProcessor::class, 'EzSystems\EzPlatformContentForms\Form\Processor\ContentFormProcessor');
