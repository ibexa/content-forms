<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Validator\Constraints;

use Ibexa\ContentForms\Validator\Constraints\Password;
use Ibexa\ContentForms\Validator\Constraints\PasswordValidator;
use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{
    /** @var \Ibexa\ContentForms\Validator\Constraints\Password */
    private $constraint;

    protected function setUp(): void
    {
        $this->constraint = new Password();
    }

    public function testConstruct()
    {
        self::assertSame('ez.user.password.invalid', $this->constraint->message);
    }

    public function testValidatedBy()
    {
        self::assertSame(PasswordValidator::class, $this->constraint->validatedBy());
    }

    public function testGetTargets()
    {
        self::assertSame([Password::CLASS_CONSTRAINT, Password::PROPERTY_CONSTRAINT], $this->constraint->getTargets());
    }
}
