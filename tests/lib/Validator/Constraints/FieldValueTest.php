<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Validator\Constraints;

use Ibexa\ContentForms\Validator\Constraints\FieldValue;
use Ibexa\ContentForms\Validator\Constraints\FieldValueValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

final class FieldValueTest extends TestCase
{
    public function testConstruct(): void
    {
        $constraint = new FieldValue();
        self::assertSame('ez.field.value', $constraint->message);
    }

    public function testValidatedBy(): void
    {
        $constraint = new FieldValue();
        self::assertSame(FieldValueValidator::class, $constraint->validatedBy());
    }

    public function testGetTargets(): void
    {
        $constraint = new FieldValue();
        self::assertSame(Constraint::CLASS_CONSTRAINT, $constraint->getTargets());
    }
}
