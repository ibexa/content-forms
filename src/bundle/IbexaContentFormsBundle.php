<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\ContentForms;

use Ibexa\Bundle\ContentForms\DependencyInjection\Compiler\FieldTypeFormMapperDispatcherPass;
use Ibexa\Bundle\ContentForms\DependencyInjection\Configuration\Parser\ContentCreateView;
use Ibexa\Bundle\ContentForms\DependencyInjection\Configuration\Parser\ContentEdit;
use Ibexa\Bundle\ContentForms\DependencyInjection\Configuration\Parser\ContentEditView;
use Ibexa\Bundle\ContentForms\DependencyInjection\Configuration\Parser\UserEdit;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IbexaContentFormsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new FieldTypeFormMapperDispatcherPass());

        $eZExtension = $container->getExtension('ibexa');
        $eZExtension->addConfigParser(new ContentEdit());
        $eZExtension->addConfigParser(new UserEdit());
        $eZExtension->addConfigParser(new ContentEditView());
        $eZExtension->addConfigParser(new ContentCreateView());
        $eZExtension->addDefaultSettings(__DIR__ . '/Resources/config', ['ezpublish_default_settings.yaml']);
    }
}

class_alias(IbexaContentFormsBundle::class, 'EzSystems\EzPlatformContentFormsBundle\EzPlatformContentFormsBundle');
