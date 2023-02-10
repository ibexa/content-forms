<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Content\View\Filter;

use Ibexa\ContentForms\Data\Content\ContentCreateData;
use Ibexa\ContentForms\Data\Mapper\ContentCreateMapper;
use Ibexa\ContentForms\Form\Type\Content\ContentEditType;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Ibexa\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent;
use Ibexa\Core\MVC\Symfony\View\ViewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class ContentCreateViewFilter implements EventSubscriberInterface
{
    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $locationService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $formFactory;

    /** @var \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    private $languagePreferenceProvider;

    /**
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface $languagePreferenceProvider
     */
    public function __construct(
        LocationService $locationService,
        ContentTypeService $contentTypeService,
        FormFactoryInterface $formFactory,
        UserLanguagePreferenceProviderInterface $languagePreferenceProvider
    ) {
        $this->locationService = $locationService;
        $this->contentTypeService = $contentTypeService;
        $this->formFactory = $formFactory;
        $this->languagePreferenceProvider = $languagePreferenceProvider;
    }

    public static function getSubscribedEvents()
    {
        return [ViewEvents::FILTER_BUILDER_PARAMETERS => 'handleContentCreateForm'];
    }

    /**
     * @param \Ibexa\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent $event
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function handleContentCreateForm(FilterViewBuilderParametersEvent $event)
    {
        if ('ibexa_content_edit:createWithoutDraftAction' !== $event->getParameters()->get('_controller')) {
            return;
        }

        $request = $event->getRequest();
        $languageCode = $request->attributes->get('language');

        $contentType = $this->contentTypeService->loadContentTypeByIdentifier(
            $request->attributes->get('contentTypeIdentifier'),
            $this->languagePreferenceProvider->getPreferredLanguages()
        );
        $location = $this->locationService->loadLocation(
            $request->attributes->getInt('parentLocationId')
        );

        $contentCreateData = $this->resolveContentCreateData($contentType, $location, $languageCode);
        $form = $this->resolveContentCreateForm(
            $contentCreateData,
            $languageCode,
            false
        );

        $event->getParameters()->add(['form' => $form->handleRequest($request)]);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     * @param string $languageCode
     *
     * @return \Ibexa\ContentForms\Data\Content\ContentCreateData
     */
    private function resolveContentCreateData(
        ContentType $contentType,
        Location $location,
        string $languageCode
    ): ContentCreateData {
        $contentCreateMapper = new ContentCreateMapper();

        return $contentCreateMapper->mapToFormData(
            $contentType,
            [
                'mainLanguageCode' => $languageCode,
                'parentLocation' => $this->locationService->newLocationCreateStruct($location->id, $contentType),
            ]
        );
    }

    /**
     * @param \Ibexa\ContentForms\Data\Content\ContentCreateData $contentCreateData
     * @param string $languageCode
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function resolveContentCreateForm(
        ContentCreateData $contentCreateData,
        string $languageCode,
        bool $autosaveEnabled = true
    ): FormInterface {
        return $this->formFactory->create(ContentEditType::class, $contentCreateData, [
            'languageCode' => $languageCode,
            'mainLanguageCode' => $languageCode,
            'contentCreateStruct' => $contentCreateData,
            'drafts_enabled' => true,
            'autosave_enabled' => $autosaveEnabled,
        ]);
    }
}

class_alias(ContentCreateViewFilter::class, 'EzSystems\EzPlatformContentForms\Content\View\Filter\ContentCreateViewFilter');
