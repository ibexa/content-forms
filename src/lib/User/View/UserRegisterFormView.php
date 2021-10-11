<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\User\View;

use EzSystems\EzPlatformUser\View\Register\FormView as BaseUserRegisterFormView;

/**
 * @deprecated Deprecated in 2.5 and will be removed in 3.0. Please use EzSystems\EzPlatformUser\View\UserRegisterFormView instead.
 */
class UserRegisterFormView extends BaseUserRegisterFormView
{
}

class_alias(UserRegisterFormView::class, 'EzSystems\EzPlatformContentForms\User\View\UserRegisterFormView');
