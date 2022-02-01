<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Content\View\Builder;

use Ibexa\ContentForms\Form\ActionDispatcher\ActionDispatcherInterface;
use Ibexa\Contracts\ContentForms\Content\Form\Provider\GroupedContentFormFieldsProviderInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Ibexa\Core\MVC\Symfony\View\Configurator;
use Ibexa\Core\MVC\Symfony\View\ParametersInjector;

/*
 * @internal
 */
abstract class AbstractContentViewBuilder
{
    /** @var \Ibexa\Contracts\Core\Repository\Repository */
    protected $repository;

    /** @var \Ibexa\Core\MVC\Symfony\View\Configurator */
    protected $viewConfigurator;

    /** @var \Ibexa\Core\MVC\Symfony\View\ParametersInjector */
    protected $viewParametersInjector;

    /** @var \Ibexa\ContentForms\Form\ActionDispatcher\ActionDispatcherInterface */
    protected $contentActionDispatcher;

    /** @var \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    protected $languagePreferenceProvider;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    protected $configResolver;

    /** @var \Ibexa\Contracts\ContentForms\Content\Form\Provider\GroupedContentFormFieldsProviderInterface */
    protected $groupedContentFormFieldsProvider;

    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    protected $contentService;

    public function __construct(
        Repository $repository,
        Configurator $viewConfigurator,
        ParametersInjector $viewParametersInjector,
        ActionDispatcherInterface $contentActionDispatcher,
        UserLanguagePreferenceProviderInterface $languagePreferenceProvider,
        ConfigResolverInterface $configResolver,
        GroupedContentFormFieldsProviderInterface $groupedContentFormFieldsProvider,
        ContentService $contentService
    ) {
        $this->repository = $repository;
        $this->viewConfigurator = $viewConfigurator;
        $this->viewParametersInjector = $viewParametersInjector;
        $this->contentActionDispatcher = $contentActionDispatcher;
        $this->languagePreferenceProvider = $languagePreferenceProvider;
        $this->configResolver = $configResolver;
        $this->groupedContentFormFieldsProvider = $groupedContentFormFieldsProvider;
        $this->contentService = $contentService;
    }

    /**
     * Loads a visible Location.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    protected function loadLocation(int $locationId): Location
    {
        return $this->repository->getLocationService()->loadLocation($locationId);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function loadLanguage(string $languageCode): Language
    {
        return $this->repository->getContentLanguageService()->loadLanguage($languageCode);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    protected function resolveLanguage(array $parameters): Language
    {
        if (isset($parameters['languageCode'])) {
            return $this->loadLanguage($parameters['languageCode']);
        }

        if (isset($parameters['language'])) {
            if (is_string($parameters['language'])) {
                // @todo BC: route parameter should be called languageCode but it won't happen until 3.0
                return $this->loadLanguage($parameters['language']);
            }

            return $parameters['language'];
        }

        throw new InvalidArgumentException(
            'Language',
            'No language information provided. Are you missing language or languageCode parameters?'
        );
    }
}

class_alias(AbstractContentViewBuilder::class, 'EzSystems\EzPlatformContentForms\Content\View\Builder\AbstractContentViewBuilder');
