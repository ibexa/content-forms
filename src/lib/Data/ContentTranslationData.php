<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data;

use Ibexa\Core\Repository\Values\Content\ContentUpdateStruct;

final class ContentTranslationData extends ContentUpdateStruct implements NewnessCheckable
{
    public function isNew(): bool
    {
        return false;
    }
}
