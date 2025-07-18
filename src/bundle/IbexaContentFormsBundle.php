<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\ContentForms;

use Ibexa\Bundle\ContentForms\DependencyInjection\Compiler\FieldTypeFormMapperDispatcherPass;
use Ibexa\Bundle\ContentForms\DependencyInjection\Configuration\Parser\ContentCreateView;
use Ibexa\Bundle\ContentForms\DependencyInjection\Configuration\Parser\ContentEditView;
use Ibexa\Bundle\ContentForms\DependencyInjection\Configuration\Parser\UserEdit;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class IbexaContentFormsBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new FieldTypeFormMapperDispatcherPass());

        /** @var \Ibexa\Bundle\Core\DependencyInjection\IbexaCoreExtension $ibexaCore */
        $ibexaCore = $container->getExtension('ibexa');

        $ibexaCore->addConfigParser(new UserEdit());
        $ibexaCore->addConfigParser(new ContentEditView());
        $ibexaCore->addConfigParser(new ContentCreateView());
        $ibexaCore->addDefaultSettings(__DIR__ . '/Resources/config', ['ibexa_core_default_settings.yaml']);
    }
}
