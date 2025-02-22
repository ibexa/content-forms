<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data;

use Ibexa\Core\Repository\Values\Content\ContentUpdateStruct;
use Override;

/**
 * @property \Ibexa\Contracts\ContentForms\Data\Content\FieldData[] $fieldsData
 * @property \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
 */
class ContentTranslationData extends ContentUpdateStruct implements NewnessCheckable
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isNew(): bool
    {
        return false;
    }
}
