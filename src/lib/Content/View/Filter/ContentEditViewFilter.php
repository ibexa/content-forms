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
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Ibexa\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent;
use Ibexa\Core\MVC\Symfony\View\ViewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class ContentEditViewFilter implements EventSubscriberInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $formFactory;

    /** @var \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    private $languagePreferenceProvider;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface $languagePreferenceProvider
     */
    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        FormFactoryInterface $formFactory,
        UserLanguagePreferenceProviderInterface $languagePreferenceProvider
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->formFactory = $formFactory;
        $this->languagePreferenceProvider = $languagePreferenceProvider;
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
        if ('ez_content_edit:editVersionDraftAction' !== $event->getParameters()->get('_controller')) {
            return;
        }

        $request = $event->getRequest();
        $languageCode = $request->attributes->get('language');
        $contentDraft = $this->contentService->loadContent(
            $request->attributes->getInt('contentId'),
            [$languageCode], // @todo: rename to languageCode in 3.0
            $request->attributes->getInt('versionNo')
        );

        $contentType = $this->contentTypeService->loadContentType(
            $contentDraft->contentInfo->contentTypeId,
            $this->languagePreferenceProvider->getPreferredLanguages()
        );

        $contentUpdate = $this->resolveContentEditData($contentDraft, $languageCode, $contentType);
        $form = $this->resolveContentEditForm(
            $contentUpdate,
            $languageCode,
            $contentDraft
        );

        $event->getParameters()->add([
            'form' => $form->handleRequest($request),
            'validate' => (bool)$request->get('validate', false),
        ]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     * @param string $languageCode
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     *
     * @return \Ibexa\ContentForms\Data\Content\ContentUpdateData
     */
    private function resolveContentEditData(
        Content $content,
        string $languageCode,
        ContentType $contentType
    ): ContentUpdateData {
        $contentUpdateMapper = new ContentUpdateMapper();

        return $contentUpdateMapper->mapToFormData($content, [
            'languageCode' => $languageCode,
            'contentType' => $contentType,
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
        Content $content
    ): FormInterface {
        return $this->formFactory->create(
            ContentEditType::class,
            $contentUpdate,
            [
                'languageCode' => $languageCode,
                'mainLanguageCode' => $content->contentInfo->mainLanguageCode,
                'content' => $content,
                'contentUpdateStruct' => $contentUpdate,
                'drafts_enabled' => true,
            ]
        );
    }
}

class_alias(ContentEditViewFilter::class, 'EzSystems\EzPlatformContentForms\Content\View\Filter\ContentEditViewFilter');
