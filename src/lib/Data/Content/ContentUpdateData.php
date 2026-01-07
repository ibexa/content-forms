<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\Content;

use Ibexa\ContentForms\Data\NewnessCheckable;
use Ibexa\ContentForms\Data\VersionInfoAwareInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\Content\ContentUpdateStruct;

class ContentUpdateData extends ContentUpdateStruct implements NewnessCheckable, VersionInfoAwareInterface
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

    public function getVersionInfo(): VersionInfo
    {
        return $this->contentDraft->versionInfo;
    }
}
