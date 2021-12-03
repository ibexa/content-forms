<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Content\View;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

/**
 * A view that contains a Content.
 */
interface ContentTypeValueView
{
    /**
     * Returns the ContentType.
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType
     */
    public function getContentType(): ContentType;
}

class_alias(ContentTypeValueView::class, 'EzSystems\EzPlatformContentForms\Content\View\ContentTypeValueView');
