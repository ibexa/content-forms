<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\ContentForms\Data\Content;

use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;

class FieldData extends ValueObject
{
    protected Field $field;

    protected FieldDefinition $fieldDefinition;

    public mixed $value;

    public function getField(): Field
    {
        return $this->field;
    }

    public function getFieldDefinition(): FieldDefinition
    {
        return $this->fieldDefinition;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getFieldTypeIdentifier(): string
    {
        return $this->fieldDefinition->getFieldTypeIdentifier();
    }
}
