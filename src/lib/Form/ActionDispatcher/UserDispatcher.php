<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\ActionDispatcher;

use Ibexa\ContentForms\Event\ContentFormEvents;

class UserDispatcher extends AbstractActionDispatcher
{
    protected function getActionEventBaseName()
    {
        return ContentFormEvents::USER_EDIT;
    }
}

class_alias(UserDispatcher::class, 'EzSystems\EzPlatformContentForms\Form\ActionDispatcher\UserDispatcher');
