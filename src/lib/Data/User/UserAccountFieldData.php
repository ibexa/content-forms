<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\User;

/**
 * User account field data value object.
 *
 * Used to store submitted user account values, since the clear password is not meant to be part of the
 * User\Value object.
 */
class UserAccountFieldData
{
    /**
     * @param string $username
     * @param string $password
     * @param string $email
     * @param bool $enabled
     */
    public function __construct(
        public $username,
        public $password,
        public $email,
        public $enabled = true
    ) {
    }
}
