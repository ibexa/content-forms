<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\ContentForms\Content\Form\Provider;

use Symfony\Component\Form\FormInterface;

interface GroupedContentFormFieldsProviderInterface
{
    /**
     * @param FormInterface<mixed>[] $fieldsDataForm
     *
     * @phpstan-return array<string, array<int, string>> Field identifiers grouped by field group identifier.
     */
    public function getGroupedFields(array $fieldsDataForm): array;
}
