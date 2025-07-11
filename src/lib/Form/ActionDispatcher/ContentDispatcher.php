<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\ActionDispatcher;

use Ibexa\ContentForms\Event\ContentFormEvents;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentDispatcher extends AbstractActionDispatcher
{
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['referrerLocation']);
        $resolver->setDefault('referrerLocation', null);
        $resolver->setAllowedTypes('referrerLocation', [Location::class, 'null']);
    }

    protected function getActionEventBaseName(): string
    {
        return ContentFormEvents::CONTENT_EDIT;
    }
}
