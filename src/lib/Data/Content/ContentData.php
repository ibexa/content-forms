<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\Content;

use Ibexa\Contracts\ContentForms\Data\Content\FieldData;

trait ContentData
{
    /**
     * @var \Ibexa\Contracts\ContentForms\Data\Content\FieldData[]
     */
    protected array $fieldsData;

    public function addFieldData(FieldData $fieldData): void
    {
        $this->fieldsData[$fieldData->fieldDefinition->identifier] = $fieldData;
    }
}
