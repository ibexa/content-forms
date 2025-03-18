<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Validator\Constraints;

use Ibexa\ContentForms\Validator\Constraints\UserAccountPassword;
use Ibexa\ContentForms\Validator\Constraints\UserAccountPasswordValidator;
use PHPUnit\Framework\TestCase;

class UserAccountPasswordTest extends TestCase
{
    /** @var \Ibexa\ContentForms\Validator\Constraints\Password */
    private UserAccountPassword $constraint;

    protected function setUp(): void
    {
        $this->constraint = new UserAccountPassword();
    }

    public function testConstruct(): void
    {
        self::assertSame('ez.user.password.invalid', $this->constraint->message);
    }

    public function testValidatedBy(): void
    {
        self::assertSame(UserAccountPasswordValidator::class, $this->constraint->validatedBy());
    }

    public function testGetTargets(): void
    {
        self::assertSame([UserAccountPassword::CLASS_CONSTRAINT, UserAccountPassword::PROPERTY_CONSTRAINT], $this->constraint->getTargets());
    }
}
