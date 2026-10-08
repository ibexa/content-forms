<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\Content;

use Ibexa\ContentForms\Data\NewnessCheckable;
use Ibexa\ContentForms\Data\VersionInfoAwareInterface;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\ContentUpdateStruct;
use Ibexa\Core\Repository\Values\Content\VersionInfo;

/**
 * @property FieldData[] $fieldsData
 * @property Content $contentDraft
 */
class ContentUpdateData extends ContentUpdateStruct implements NewnessCheckable, VersionInfoAwareInterface
{
    use ContentData;

    protected $contentDraft;

    public function isNew()
    {
        return false;
    }

    public function getVersionInfo(): VersionInfo
    {
        return $this->contentDraft->versionInfo;
    }
}

class_alias(ContentUpdateData::class, 'EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData');
