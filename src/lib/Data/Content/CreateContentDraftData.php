<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\Content;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;

final class CreateContentDraftData extends ValueObject
{
    public int $contentId;

    public int $fromVersionNo;

    public string $fromLanguage;

    public string $toLanguage;
}
