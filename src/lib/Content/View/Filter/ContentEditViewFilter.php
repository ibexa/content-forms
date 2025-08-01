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

final readonly class ContentEditViewFilter implements EventSubscriberInterface
{
    public function __construct(
        private ContentService $contentService,
        private LocationService $locationService,
        private ContentTypeService $contentTypeService,
        private FormFactoryInterface $formFactory,
        private UserLanguagePreferenceProviderInterface $languagePreferenceProvider
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [ViewEvents::FILTER_BUILDER_PARAMETERS => 'handleContentEditForm'];
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function handleContentEditForm(FilterViewBuilderParametersEvent $event): void
    {
        if ('ibexa_content_edit::editVersionDraftAction' !== $event->getParameters()->get('_controller')) {
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
        $currentFields = iterator_to_array($currentContent->getFields());

        $contentType = $this->contentTypeService->loadContentType(
            $contentDraft->getContentInfo()->getContentType()->id,
            $this->languagePreferenceProvider->getPreferredLanguages()
        );

        try {
            $location = $this->locationService->loadLocation(
                (int)$event->getParameters()->get(
                    'locationId',
                    $contentDraft->getContentInfo()->getMainLocationId()
                )
            );
        } catch (NotFoundException) {
            //do nothing
        }

        $contentUpdate = $this->resolveContentEditData(
            $contentDraft,
            $languageCode,
            $contentType,
            $currentFields
        );

        $form = $this->resolveContentEditForm(
            $contentUpdate,
            $languageCode,
            $contentDraft,
            $location ?? null
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
     * @return \Symfony\Component\Form\FormInterface<mixed>
     */
    private function resolveContentEditForm(
        ContentUpdateData $contentUpdate,
        string $languageCode,
        Content $content,
        ?Location $location = null
    ): FormInterface {
        return $this->formFactory->create(
            ContentEditType::class,
            $contentUpdate,
            [
                'location' => $location,
                'languageCode' => $languageCode,
                'mainLanguageCode' => $content->getContentInfo()->getMainLanguageCode(),
                'content' => $content,
                'contentUpdateStruct' => $contentUpdate,
                'struct' => $contentUpdate,
                'drafts_enabled' => true,
            ]
        );
    }
}
