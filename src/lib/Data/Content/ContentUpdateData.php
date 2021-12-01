<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\Content;

use Ibexa\Core\Repository\Values\Content\ContentUpdateStruct;
use Ibexa\ContentForms\Data\NewnessCheckable;

/**
 * @property \EzSystems\EzPlatformContentForms\Data\Content\FieldData[] $fieldsData
 * @property \eZ\Publish\API\Repository\Values\Content\Content $contentDraft
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
