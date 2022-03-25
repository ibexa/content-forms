<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\User;

use Ibexa\ContentForms\Data\Content\ContentData;
use Ibexa\ContentForms\Data\NewnessCheckable;
use Ibexa\Contracts\Core\Repository\Values\User\UserUpdateStruct;

/**
 * @property \Ibexa\Contracts\ContentForms\Data\Content\FieldData[] $fieldsData
 * @property \Ibexa\Contracts\Core\Repository\Values\User\User $user
 */
class UserUpdateData extends UserUpdateStruct implements NewnessCheckable
{
    use ContentData;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\User\User
     */
    public $user;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType
     */
    public $contentType;

    public function isNew()
    {
        return false;
    }
}

class_alias(UserUpdateData::class, 'EzSystems\EzPlatformContentForms\Data\User\UserUpdateData');
