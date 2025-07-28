<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Content\View\Provider\ContentEditView;

use Ibexa\ContentForms\Content\View\ContentEditView;
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

    public function getView(View $view): ?ContentEditView
    {
        if (($configHash = $this->matcherFactory->match($view)) === null) {
            return null;
        }

        return $this->buildContentEditView($configHash);
    }

    /**
     * Builds a ContentEditView object from $viewConfig.
     *
     * @param array<string, mixed> $viewConfig
     */
    private function buildContentEditView(array $viewConfig): ContentEditView
    {
        $view = new ContentEditView();
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
