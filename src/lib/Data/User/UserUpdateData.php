<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\User;

use Ibexa\ContentForms\Data\Content\ContentData;
use Ibexa\ContentForms\Data\NewnessCheckable;
use Ibexa\ContentForms\Data\VersionInfoAwareInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Contracts\Core\Repository\Values\User\UserUpdateStruct;

class UserUpdateData extends UserUpdateStruct implements NewnessCheckable, VersionInfoAwareInterface
{
    use ContentData;

    public User $user;

    public ContentType $contentType;

    public function isNew(): bool
    {
        return false;
    }

    public function getVersionInfo(): VersionInfo
    {
        return $this->user->versionInfo;
    }
}
