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
use Symfony\Component\HttpKernel\Controller\ControllerReference;

/**
 * View provider based on configuration.
 */
final readonly class Configured implements ViewProvider
{
    public function __construct(private MatcherFactoryInterface $matcherFactory)
    {
    }

    public function getView(View $view): ?ContentCreateView
    {
        if (($configHash = $this->matcherFactory->match($view)) === null) {
            return null;
        }

        return $this->buildContentCreateView($configHash);
    }

    /**
     * @param array<string, mixed> $viewConfig
     */
    private function buildContentCreateView(array $viewConfig): ContentCreateView
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
