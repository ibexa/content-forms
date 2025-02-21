<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Content\Form\Provider;

class IdentifiedGroupedContentFormFieldsProvider extends AbstractGroupedContentFormFieldsProvider
{
    protected function prepareGroupContext(): array
    {
        return [];
    }

    protected function getGroupKey(string $fieldGroupIdentifier, array $groupContext): string
    {
        return $fieldGroupIdentifier;
    }
}
