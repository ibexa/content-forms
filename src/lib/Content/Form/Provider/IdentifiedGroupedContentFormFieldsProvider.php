<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Content\Form\Provider;

final class IdentifiedGroupedContentFormFieldsProvider extends AbstractGroupedContentFormFieldsProvider
{
    protected function getGroupKey(string $fieldGroupIdentifier): string
    {
        return $fieldGroupIdentifier;
    }
}
