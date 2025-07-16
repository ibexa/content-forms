<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\ContentForms\FieldType;

use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Symfony\Component\Form\FormInterface;

interface FieldValueFormMapperInterface
{
    /**
     * Maps Field form to current FieldType.
     * Allows to add form fields for content edition.
     */
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data);
}
