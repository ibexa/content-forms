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

final readonly class ContentCreateViewFilter implements EventSubscriberInterface
{
    public function __construct(
        private LocationService $locationService,
        private ContentTypeService $contentTypeService,
        private FormFactoryInterface $formFactory,
        private UserLanguagePreferenceProviderInterface $languagePreferenceProvider
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [ViewEvents::FILTER_BUILDER_PARAMETERS => 'handleContentCreateForm'];
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function handleContentCreateForm(FilterViewBuilderParametersEvent $event): void
    {
        if ('ibexa_content_edit::createWithoutDraftAction' !== $event->getParameters()->get('_controller')) {
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
                'parentLocation' => $this->locationService->newLocationCreateStruct($location->id),
            ]
        );
    }

    /**
     * @return \Symfony\Component\Form\FormInterface<mixed>
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
            'struct' => $contentCreateData,
        ]);
    }
}
