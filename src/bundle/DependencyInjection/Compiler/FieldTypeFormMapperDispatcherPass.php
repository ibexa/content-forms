<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\ContentForms\DependencyInjection\Compiler;

use Ibexa\ContentForms\FieldType\FieldTypeFormMapperDispatcher;
use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register FieldType form mappers in the mapper dispatcher.
 */
class FieldTypeFormMapperDispatcherPass implements CompilerPassInterface
{
    public const FIELD_TYPE_FORM_MAPPER_DISPATCHER = FieldTypeFormMapperDispatcher::class;
    public const FIELD_TYPE_FORM_MAPPER_VALUE_SERVICE_TAG = 'ibexa.admin_ui.field_type.form.mapper.value';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::FIELD_TYPE_FORM_MAPPER_DISPATCHER)) {
            return;
        }

        $dispatcherDefinition = $container->findDefinition(self::FIELD_TYPE_FORM_MAPPER_DISPATCHER);

        $taggedServiceIds = $container->findTaggedServiceIds(
            self::FIELD_TYPE_FORM_MAPPER_VALUE_SERVICE_TAG
        );
        foreach ($taggedServiceIds as $serviceId => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['fieldType'])) {
                    throw new LogicException(
                        sprintf(
                            'Service "%s" tagged with "%s" service tag needs a "fieldType" ' .
                            'attribute to identify which Field Type the mapper is for.',
                            $serviceId,
                            self::FIELD_TYPE_FORM_MAPPER_VALUE_SERVICE_TAG
                        )
                    );
                }

                $dispatcherDefinition->addMethodCall('addMapper', [new Reference($serviceId), $tag['fieldType']]);
            }
        }
    }
}

class_alias(FieldTypeFormMapperDispatcherPass::class, 'EzSystems\EzPlatformContentFormsBundle\DependencyInjection\Compiler\FieldTypeFormMapperDispatcherPass');
