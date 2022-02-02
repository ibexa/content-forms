<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\Content;

use Ibexa\ContentForms\Data\NewnessCheckable;
use Ibexa\Core\Repository\Values\Content\ContentUpdateStruct;

/**
 * @property \Ibexa\Contracts\ContentForms\Data\Content\FieldData[] $fieldsData
 * @property \Ibexa\Contracts\Core\Repository\Values\Content\Content $contentDraft
 */
class ContentUpdateData extends ContentUpdateStruct implements NewnessCheckable
{
    use ContentData;

    protected $contentDraft;

    public function isNew()
    {
        return false;
    }
}

class_alias(ContentUpdateData::class, 'EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData');
