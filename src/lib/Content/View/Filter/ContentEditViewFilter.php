<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Content\View\Filter;

use Ibexa\ContentForms\Data\Content\ContentUpdateData;
use Ibexa\ContentForms\Data\Mapper\ContentUpdateMapper;
use Ibexa\ContentForms\Form\Type\Content\ContentEditType;
use Ibexa\Contracts\ContentForms\Event\AutosaveEnabled;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Ibexa\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent;
use Ibexa\Core\MVC\Symfony\View\ViewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ContentEditViewFilter implements EventSubscriberInterface
{
    private ContentService $contentService;

    private ContentTypeService $contentTypeService;

    private FormFactoryInterface $formFactory;

    private UserLanguagePreferenceProviderInterface $languagePreferenceProvider;

    private LocationService $locationService;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        ContentService $contentService,
        LocationService $locationService,
        ContentTypeService $contentTypeService,
        FormFactoryInterface $formFactory,
        UserLanguagePreferenceProviderInterface $languagePreferenceProvider,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->formFactory = $formFactory;
        $this->languagePreferenceProvider = $languagePreferenceProvider;
        $this->locationService = $locationService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [ViewEvents::FILTER_BUILDER_PARAMETERS => 'handleContentEditForm'];
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent $event
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function handleContentEditForm(FilterViewBuilderParametersEvent $event)
    {
        if ('ibexa_content_edit:editVersionDraftAction' !== $event->getParameters()->get('_controller')) {
            return;
        }

        $request = $event->getRequest();
        $languageCode = $request->attributes->get('language');
        $contentId = $request->attributes->getInt('contentId');
        $contentDraft = $this->contentService->loadContent(
            $contentId,
            [$languageCode], // @todo: rename to languageCode in 3.0
            $request->attributes->getInt('versionNo')
        );
        $currentContent = $this->contentService->loadContent($contentId);
        $currentFields = $currentContent->getFields();

        $contentType = $this->contentTypeService->loadContentType(
            $contentDraft->contentInfo->contentTypeId,
            $this->languagePreferenceProvider->getPreferredLanguages()
        );

        try {
            $location = $this->locationService->loadLocation(
                (int)$event->getParameters()->get(
                    'locationId',
                    $contentDraft->contentInfo->mainLocationId
                )
            );
        } catch (NotFoundException $e) {
        }

        $contentUpdate = $this->resolveContentEditData(
            $contentDraft,
            $languageCode,
            $contentType,
            $currentFields
        );

        $autosaveEnabled = $this->eventDispatcher->dispatch(new AutosaveEnabled($contentDraft->getVersionInfo()))->isAutosaveEnabled();
        $form = $this->resolveContentEditForm(
            $contentUpdate,
            $languageCode,
            $contentDraft,
            $location ?? null,
            $autosaveEnabled
        );

        $event->getParameters()->add([
            'form' => $form->handleRequest($request),
            'validate' => (bool)$request->get('validate', false),
        ]);
    }

    /**
     * @param array<\Ibexa\Contracts\Core\Repository\Values\Content\Field> $currentFields
     */
    private function resolveContentEditData(
        Content $content,
        string $languageCode,
        ContentType $contentType,
        array $currentFields
    ): ContentUpdateData {
        $contentUpdateMapper = new ContentUpdateMapper();

        return $contentUpdateMapper->mapToFormData($content, [
            'languageCode' => $languageCode,
            'contentType' => $contentType,
            'currentFields' => $currentFields,
        ]);
    }

    /**
     * @param \Ibexa\ContentForms\Data\Content\ContentUpdateData $contentUpdate
     * @param string $languageCode
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function resolveContentEditForm(
        ContentUpdateData $contentUpdate,
        string $languageCode,
        Content $content,
        ?Location $location = null,
        bool $autosaveEnabled = true
    ): FormInterface {
        return $this->formFactory->create(
            ContentEditType::class,
            $contentUpdate,
            [
                'location' => $location,
                'languageCode' => $languageCode,
                'mainLanguageCode' => $content->contentInfo->mainLanguageCode,
                'content' => $content,
                'contentUpdateStruct' => $contentUpdate,
                'drafts_enabled' => true,
                'autosave_enabled' => $autosaveEnabled,
            ]
        );
    }
}

class_alias(ContentEditViewFilter::class, 'EzSystems\EzPlatformContentForms\Content\View\Filter\ContentEditViewFilter');
