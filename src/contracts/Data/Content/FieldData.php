<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\ContentForms\Data\Content;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * @property \Ibexa\Contracts\Core\Repository\Values\Content\Field $field
 * @property \Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition $fieldDefinition
 */
class FieldData extends ValueObject
{
    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Field
     */
    protected $field;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition
     */
    protected $fieldDefinition;

    /**
     * @var mixed
     */
    public $value;

    public function getFieldTypeIdentifier()
    {
        return $this->fieldDefinition->fieldTypeIdentifier;
    }
}
