<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\ContentForms\DependencyInjection\Configuration\Parser;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\AbstractParser;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class UserEdit extends AbstractParser
{
    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->arrayNode('user_edit')
                ->info('Content edit configuration')
                ->children()
                    ->arrayNode('templates')
                        ->info('Content edit templates.')
                        ->children()
                            ->scalarNode('update')
                                ->info('Template to use for user edit form rendering.')
                            ->end()
                            ->scalarNode('create')
                                ->info('Template to use for user create form rendering.')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array<string, mixed> $scopeSettings
     */
    public function mapConfig(array &$scopeSettings, mixed $currentScope, ContextualizerInterface $contextualizer): void
    {
        if (empty($scopeSettings['user_edit'])) {
            return;
        }

        $settings = $scopeSettings['user_edit'];

        if (!empty($settings['templates']['update'])) {
            $contextualizer->setContextualParameter(
                'user_edit.templates.update',
                $currentScope,
                $settings['templates']['update']
            );
        }

        if (!empty($settings['templates']['create'])) {
            $contextualizer->setContextualParameter(
                'user_edit.templates.create',
                $currentScope,
                $settings['templates']['create']
            );
        }
    }
}
