<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\RawMinkContext;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use PHPUnit\Framework\Assert as Assertion;

final class PagelayoutContext extends RawMinkContext implements Context, SnippetAcceptingContext
{
    /** @var string Regex matching the way the Twig template name is inserted in debug mode */
    public const string TWIG_DEBUG_STOP_REGEX = '<!-- STOP .*%s.* -->';

    public function __construct(
        private readonly ConfigResolverInterface $configResolver
    ) {
    }

    /**
     * @Given /^a pagelayout is configured$/
     */
    public function aPagelayoutIsConfigured(): void
    {
        Assertion::assertTrue($this->configResolver->hasParameter('page_layout'));
    }

    /**
     * @Then /^it is rendered using the configured pagelayout$/
     */
    public function itIsRenderedUsingTheConfiguredPagelayout(): void
    {
        $pageLayout = $this->getPageLayout();

        $searchedPattern = sprintf(self::TWIG_DEBUG_STOP_REGEX, preg_quote($pageLayout, null));
        Assertion::assertMatchesRegularExpression($searchedPattern, $this->getSession()->getPage()->getOuterHtml());
    }

    public function getPageLayout(): string
    {
        return $this->configResolver->getParameter('page_layout', null, 'site');
    }
}
