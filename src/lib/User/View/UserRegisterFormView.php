<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\User\View;

use Ibexa\User\View\Register\FormView as BaseUserRegisterFormView;

/**
 * @deprecated Deprecated in 2.5 and will be removed in 3.0. Please use Ibexa\User\View\UserRegisterFormView instead.
 */
class UserRegisterFormView extends BaseUserRegisterFormView
{
}

class_alias(UserRegisterFormView::class, 'EzSystems\EzPlatformContentForms\User\View\UserRegisterFormView');
