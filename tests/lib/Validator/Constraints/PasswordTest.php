<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Validator\Constraints;

use Ibexa\ContentForms\Validator\Constraints\Password;
use Ibexa\ContentForms\Validator\Constraints\PasswordValidator;
use Override;
use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{
    private Password $constraint;

    #[Override]
    protected function setUp(): void
    {
        $this->constraint = new Password();
    }

    public function testConstruct(): void
    {
        self::assertSame('ez.user.password.invalid', $this->constraint->message);
    }

    public function testValidatedBy(): void
    {
        self::assertSame(PasswordValidator::class, $this->constraint->validatedBy());
    }

    public function testGetTargets(): void
    {
        self::assertSame([Password::CLASS_CONSTRAINT, Password::PROPERTY_CONSTRAINT], $this->constraint->getTargets());
    }
}
