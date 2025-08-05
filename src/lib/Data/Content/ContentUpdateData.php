<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\Content;

use Ibexa\ContentForms\Data\NewnessCheckable;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\ContentUpdateStruct;

class ContentUpdateData extends ContentUpdateStruct implements NewnessCheckable
{
    use ContentData;

    protected Content $contentDraft;

    public function isNew(): bool
    {
        return false;
    }

    public function getContentDraft(): Content
    {
        return $this->contentDraft;
    }
}
