<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Validator\Constraints;

class UserAccountPassword extends Password
{
}

class_alias(UserAccountPassword::class, 'EzSystems\EzPlatformContentForms\Validator\Constraints\UserAccountPassword');
