<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Content\View\Builder;

use Ibexa\ContentForms\Content\View\ContentEditSuccessView;
use Ibexa\ContentForms\Content\View\ContentEditView;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Base\Exceptions\InvalidArgumentType;
use Ibexa\Core\MVC\Symfony\View\Builder\ViewBuilder;
use Ibexa\Core\MVC\Symfony\View\ContentView;
use Ibexa\Core\MVC\Symfony\View\View;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * Builds ContentEditView objects.
 *
 * @internal
 */
class ContentEditViewBuilder extends AbstractContentViewBuilder implements ViewBuilder
{
    public function matches($argument)
    {
        return 'ibexa_content_edit:editVersionDraftAction' === $argument;
    }

    /**
     * @param array $parameters
     *
     * @return ContentView|View
     *
     * @throws InvalidArgumentType
     * @throws InvalidOptionsException
     * @throws BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws UnauthorizedException
     */
    public function buildView(array $parameters)
    {
        $view = new ContentEditView($this->configResolver->getParameter('content_edit.templates.edit'));

        $language = $this->resolveLanguage($parameters);
        $location = $this->resolveLocation($parameters);
        $content = $this->resolveContent($parameters, $location, $language);
        $contentInfo = $content->contentInfo;
        $contentType = $this->loadContentType((int) $contentInfo->contentTypeId, $this->languagePreferenceProvider->getPreferredLanguages());
        /** @var Form $form */
        $form = $parameters['form'];
        $isPublished = null !== $contentInfo->mainLocationId && $contentInfo->published;

        if (!$content->getVersionInfo()->isDraft()) {
            throw new InvalidArgumentException('Version', 'The status is not draft');
        }

        if (null === $location && $isPublished) {
            try {
                // assume main location if no location was provided
                $location = $this->loadLocation((int)$contentInfo->mainLocationId);
            } catch (UnauthorizedException $e) {
                // if no access to the main location assume content has multiple locations and first of them can be used
                $availableLocations = $this->repository->getLocationService()->loadLocations($contentInfo);
                $location = array_shift($availableLocations);
            }
        }

        if (null !== $location && $location->contentId !== $content->id) {
            throw new InvalidArgumentException('Location', 'The provided Location does not belong to the selected content');
        }

        if ($form->isSubmitted() && $form->isValid() && null !== $form->getClickedButton()) {
            $this->contentActionDispatcher->dispatchFormAction(
                $form,
                $form->getData(),
                $form->getClickedButton()->getName(),
                ['referrerLocation' => $location]
            );

            if ($response = $this->contentActionDispatcher->getResponse()) {
                $view = new ContentEditSuccessView($response);
                $view->setLocation($location);

                return $view;
            }
        }

        if ($parameters['validate'] && !$form->isSubmitted()) {
            $validationErrors = $this->contentService->validate(
                $content->getVersionInfo(),
                [
                    'content' => $content,
                ]
            );

            foreach ($validationErrors as $fieldIdentifier => $validationErrorLanguages) {
                $fieldValueElement = $form->get('fieldsData')->get($fieldIdentifier)->get('value');
                foreach ($validationErrorLanguages as $validationErrors) {
                    if (is_array($validationErrors) === false) {
                        $validationErrors = [$validationErrors];
                    }
                    foreach ($validationErrors as $validationError) {
                        $fieldValueElement->addError(new FormError(
                            (string)$validationError->getTranslatableMessage()
                        ));
                    }
                }
            }
        }

        // Reload content in case it was modified by the form handler.
        $content = $this->resolveContent($parameters, $location, $language);
        $view->setContent($content);
        $view->setLanguage($language);
        $view->setLocation($location);
        $view->setForm($parameters['form']);

        $view->addParameters([
            'content' => $content,
            'location' => $location,
            'language' => $language,
            'content_type' => $contentType,
            'form' => $form->createView(),
            'grouped_fields' => $this->groupedContentFormFieldsProvider->getGroupedFields(
                $form->get('fieldsData')->all()
            ),
        ]);

        $this->viewParametersInjector->injectViewParameters($view, $parameters);
        $this->viewConfigurator->configure($view);

        return $view;
    }

    /**
     * Loads Content with id $contentId.
     *
     * @param int $contentId
     * @param array $languages
     * @param int|null $versionNo
     *
     * @return Content
     *
     * @throws UnauthorizedException
     * @throws NotFoundException
     */
    private function loadContent(
        int $contentId,
        array $languages = [],
        ?int $versionNo = null
    ): Content {
        return $this->repository->getContentService()->loadContent($contentId, $languages, $versionNo);
    }

    /**
     * Loads ContentType with id $contentTypeId.
     *
     * @param int $contentTypeId
     * @param string[] $languageCodes
     *
     * @return ContentType
     *
     * @throws NotFoundException
     */
    private function loadContentType(
        int $contentTypeId,
        array $languageCodes
    ): ContentType {
        return $this->repository->getContentTypeService()->loadContentType($contentTypeId, $languageCodes);
    }

    /**
     * @param array $parameters
     * @param Location|null $location
     * @param Language $language
     *
     * @return Content
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function resolveContent(
        array $parameters,
        ?Location $location,
        Language $language
    ): Content {
        if (isset($parameters['content'])) {
            return $parameters['content'];
        }

        if (isset($parameters['contentId'])) {
            $contentId = $parameters['contentId'];
        } elseif (null !== $location) {
            $contentId = $location->contentId;
        } else {
            throw new InvalidArgumentException(
                'Content',
                'No content could be loaded from the parameters'
            );
        }

        return $this->loadContent(
            (int) $contentId,
            null !== $language ? [$language->languageCode] : [],
            (int) $parameters['versionNo'] ?: null
        );
    }

    /**
     * @param array $parameters
     *
     * @return Location|null
     */
    private function resolveLocation(array $parameters): ?Location
    {
        if (isset($parameters['locationId'])) {
            try {
                // the load error is suppressed because a user can have no permission to this location
                // but can have access to another location when content is in multiple locations
                return $this->loadLocation((int)$parameters['locationId']);
            } catch (UnauthorizedException $e) {
            }
        }

        if (isset($parameters['location'])) {
            return $parameters['location'];
        }

        return null;
    }
}

class_alias(ContentEditViewBuilder::class, 'EzSystems\EzPlatformContentForms\Content\View\Builder\ContentEditViewBuilder');
