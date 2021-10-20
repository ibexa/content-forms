<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\Content;

use Ibexa\Contracts\ContentForms\Data\Content\FieldData;

trait ContentData
{
    /**
     * @var \EzSystems\EzPlatformContentForms\Data\Content\FieldData[]
     */
    protected $fieldsData;

    public function addFieldData(FieldData $fieldData)
    {
        $this->fieldsData[$fieldData->fieldDefinition->identifier] = $fieldData;
    }
}

class_alias(ContentData::class, 'EzSystems\EzPlatformContentForms\Data\Content\ContentData');
