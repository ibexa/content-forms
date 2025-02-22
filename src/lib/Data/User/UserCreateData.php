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
use Override;

/**
 * @property \Ibexa\Contracts\ContentForms\Data\Content\FieldData[] $fieldsData
 */
class UserCreateData extends UserCreateStruct implements NewnessCheckable
{
    use ContentData;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[]
     */
    private ?array $parentGroups = null;

    private ?Role $role = null;

    private ?RoleLimitation $roleLimitation = null;

    #[Override]
    public function isNew(): bool
    {
        return true;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[]
     */
    public function getParentGroups(): array
    {
        return $this->parentGroups;
    }

    /**
     * Adds a parent group.
     */
    public function addParentGroup(UserGroup $parentGroup): void
    {
        $this->parentGroups[] = $parentGroup;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\User\UserGroup[] $parentGroups
     */
    public function setParentGroups(array $parentGroups): void
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
