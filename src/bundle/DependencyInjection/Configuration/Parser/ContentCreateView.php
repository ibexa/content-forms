<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\ContentForms\DependencyInjection\Configuration\Parser;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\Parser\View;

final class ContentCreateView extends View
{
    public const string NODE_KEY = 'content_create_view';
    public const string INFO = 'Template selection settings when displaying a content create form';
}
