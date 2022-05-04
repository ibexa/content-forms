<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\User;

use Ibexa\ContentForms\Data\Content\ContentData;
use Ibexa\ContentForms\Data\NewnessCheckable;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\RoleLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Role;
use Ibexa\Contracts\Core\Repository\Values\User\UserGroup;
use Ibexa\Core\Repository\Values\User\UserCreateStruct;

/**
 * @property \Ibexa\Contracts\ContentForms\Data\Content\FieldData[] $fieldsData
 */
class UserCreateData extends UserCreateStruct implements NewnessCheckable
{
    use ContentData;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[]
     */
    private $parentGroups;

    private ?Role $role = null;

    private ?RoleLimitation $roleLimitation = null;

    public function isNew()
    {
        return true;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[]
     */
    public function getParentGroups()
    {
        return $this->parentGroups;
    }

    /**
     * Adds a parent group.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\User\UserGroup $parentGroup
     */
    public function addParentGroup(UserGroup $parentGroup)
    {
        $this->parentGroups[] = $parentGroup;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[] $parentGroups
     */
    public function setParentGroups(array $parentGroups)
    {
        $this->parentGroups = $parentGroups;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): void
    {
        $this->role = $role;
    }

    public function getRoleLimitation(): ?RoleLimitation
    {
        return $this->roleLimitation;
    }

    public function setRoleLimitation(?RoleLimitation $roleLimitation): void
    {
        $this->roleLimitation = $roleLimitation;
    }
}

class_alias(UserCreateData::class, 'EzSystems\EzPlatformContentForms\Data\User\UserCreateData');
