<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Validator\Constraints;

use Ibexa\ContentForms\Data\User\UserAccountFieldData;
use Ibexa\ContentForms\Validator\ValidationErrorsProcessor;
use Symfony\Component\Validator\Constraint;

class UserAccountPasswordValidator extends PasswordValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!($value instanceof UserAccountFieldData)) {
            return;
        }

        parent::validate($value->password, $constraint);
    }

    /**
     * {@inheritdoc}
     */
    protected function createValidationErrorsProcessor(): ValidationErrorsProcessor
    {
        return new ValidationErrorsProcessor($this->context, static function () {
            return 'password';
        });
    }
}

class_alias(UserAccountPasswordValidator::class, 'EzSystems\EzPlatformContentForms\Validator\Constraints\UserAccountPasswordValidator');
