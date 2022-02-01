<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class FieldValue extends Constraint
{
    public $message = 'ez.field.value';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return FieldValueValidator::class;
    }
}

class_alias(FieldValue::class, 'EzSystems\EzPlatformContentForms\Validator\Constraints\FieldValue');
