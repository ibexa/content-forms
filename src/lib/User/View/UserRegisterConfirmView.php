<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\User\View;

use Ibexa\User\View\Register\ConfirmView as BaseRegisterConfirmView;

/**
 * @deprecated Deprecated in 2.5 and will be removed in 3.0. Please use Ibexa\User\View\UserRegisterConfirmView instead.
 */
class UserRegisterConfirmView extends BaseRegisterConfirmView
{
}

class_alias(UserRegisterConfirmView::class, 'EzSystems\EzPlatformContentForms\User\View\UserRegisterConfirmView');
