<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Content\View\Provider\ContentCreateView;

use Ibexa\ContentForms\Content\View\ContentCreateView;
use Ibexa\Core\MVC\Symfony\Matcher\MatcherFactoryInterface;
use Ibexa\Core\MVC\Symfony\View\View;
use Ibexa\Core\MVC\Symfony\View\ViewProvider;
use Override;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

/**
 * View provider based on configuration.
 */
class Configured implements ViewProvider
{
    /**
     * @param \Ibexa\Core\MVC\Symfony\Matcher\MatcherFactoryInterface $matcherFactory
     */
    public function __construct(protected MatcherFactoryInterface $matcherFactory)
    {
    }

    #[Override]
    public function getView(View $view)
    {
        if (($configHash = $this->matcherFactory->match($view)) === null) {
            return null;
        }

        return $this->buildContentCreateView($configHash);
    }

    /**
     * Builds a ContentCreateView object from $viewConfig.
     *
     * @param array $viewConfig
     *
     * @return \Ibexa\ContentForms\Content\View\ContentCreateView
     */
    protected function buildContentCreateView(array $viewConfig): ContentCreateView
    {
        $view = new ContentCreateView();
        $view->setConfigHash($viewConfig);
        if (isset($viewConfig['template'])) {
            $view->setTemplateIdentifier($viewConfig['template']);
        }
        if (isset($viewConfig['controller'])) {
            $view->setControllerReference(new ControllerReference($viewConfig['controller']));
        }
        if (isset($viewConfig['params']) && is_array($viewConfig['params'])) {
            $view->addParameters($viewConfig['params']);
        }

        return $view;
    }
}
