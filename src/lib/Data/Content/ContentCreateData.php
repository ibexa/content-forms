<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\Content;

use Ibexa\ContentForms\Data\NewnessCheckable;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct;
use Ibexa\Core\Repository\Values\Content\ContentCreateStruct;

/**
 * @property \Ibexa\Contracts\ContentForms\Data\Content\FieldData[] $fieldsData
 */
class ContentCreateData extends ContentCreateStruct implements NewnessCheckable
{
    use ContentData;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct[]
     */
    private $locationStructs;

    public function isNew()
    {
        return true;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct[]
     */
    public function getLocationStructs()
    {
        return $this->locationStructs;
    }

    /**
     * Adds a location struct.
     * A location will be created out of it, bound to the created content.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct $locationStruct
     */
    public function addLocationStruct(LocationCreateStruct $locationStruct)
    {
        $this->locationStructs[] = $locationStruct;
    }
}

class_alias(ContentCreateData::class, 'EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData');
