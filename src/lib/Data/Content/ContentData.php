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
    /** @var array<string, \Ibexa\Contracts\ContentForms\Data\Content\FieldData> */
    protected array $fieldsData;

    /**
     * @return array<string, \Ibexa\Contracts\ContentForms\Data\Content\FieldData>
     */
    public function getFieldsData(): array
    {
        return $this->fieldsData;
    }

    public function addFieldData(FieldData $fieldData): void
    {
        $this->fieldsData[$fieldData->fieldDefinition->identifier] = $fieldData;
    }
}
