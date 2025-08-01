<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\ActionDispatcher;

use Ibexa\ContentForms\Event\ContentFormEvents;

final class UserDispatcher extends AbstractActionDispatcher
{
    protected function getActionEventBaseName(): string
    {
        return ContentFormEvents::USER_EDIT;
    }
}
