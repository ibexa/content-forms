<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Content\Form\Provider;

/**
 * @deprecated 4.6.17 The "GroupedContentFormFieldsProvider" class is deprecated.
 */
final class GroupedContentFormFieldsProvider extends AbstractGroupedContentFormFieldsProvider
{
    protected function getGroupKey(string $fieldGroupIdentifier): string
    {
        $this->groupContext = $this->groupContext ?: $this->fieldsGroupsList->getGroups();

        return $this->groupContext[$fieldGroupIdentifier] ?? $this->fieldsGroupsList->getDefaultGroup();
    }
}
