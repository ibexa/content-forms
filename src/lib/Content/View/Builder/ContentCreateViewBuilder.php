<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Content\View\Builder;

use Ibexa\ContentForms\Content\View\ContentCreateSuccessView;
use Ibexa\ContentForms\Content\View\ContentCreateView;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\MVC\Symfony\View\Builder\ViewBuilder;

/**
 * Builds ContentCreateView objects.
 *
 * @internal
 */
final class ContentCreateViewBuilder extends AbstractContentViewBuilder implements ViewBuilder
{
    public function matches($argument): bool
    {
        return 'ibexa_content_edit::createWithoutDraftAction' === $argument;
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function buildView(array $parameters): ContentCreateSuccessView|ContentCreateView
    {
        $view = new ContentCreateView($this->configResolver->getParameter('content_edit.templates.create'));

        $language = $this->resolveLanguage($parameters);
        $location = $this->resolveLocation($parameters);
        $contentType = $this->resolveContentType($parameters, $this->languagePreferenceProvider->getPreferredLanguages());
        /** @var \Symfony\Component\Form\Form $form */
        $form = $parameters['form'];

        if ($form->isSubmitted() && $form->isValid() && null !== $form->getClickedButton()) {
            $this->contentActionDispatcher->dispatchFormAction(
                $form,
                $form->getData(),
                $form->getClickedButton()->getName(),
                ['referrerLocation' => $location]
            );

            if ($response = $this->contentActionDispatcher->getResponse()) {
                $view = new ContentCreateSuccessView($response);
                $view->setLocation($location);

                return $view;
            }
        }

        $view->setContentType($contentType);
        $view->setLanguage($language);
        $view->setLocation($location);
        $view->setForm($form);

        $view->addParameters([
            'content_type' => $contentType,
            'language' => $language,
            'parent_location' => $location,
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
     * Loads ContentType with identifier $contentTypeIdentifier.
     *
     * @param string[] $prioritizedLanguages
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function loadContentType(string $contentTypeIdentifier, array $prioritizedLanguages = []): ContentType
    {
        return $this->repository->getContentTypeService()->loadContentTypeByIdentifier(
            $contentTypeIdentifier,
            $prioritizedLanguages
        );
    }

    /**
     * @param array<string, mixed> $parameters
     * @param string[] $languageCodes
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function resolveContentType(array $parameters, array $languageCodes): ContentType
    {
        if (isset($parameters['contentType'])) {
            return $parameters['contentType'];
        }

        if (isset($parameters['contentTypeIdentifier'])) {
            return $this->loadContentType($parameters['contentTypeIdentifier'], $languageCodes);
        }

        throw new InvalidArgumentException(
            'ContentType',
            'No content type could be loaded from the parameters'
        );
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function resolveLocation(array $parameters): Location
    {
        if (isset($parameters['parentLocation'])) {
            return $parameters['parentLocation'];
        }

        if (isset($parameters['parentLocationId'])) {
            return $this->loadLocation((int) $parameters['parentLocationId']);
        }

        throw new InvalidArgumentException(
            'ParentLocation',
            'Unable to load parent Location from the parameters'
        );
    }
}
