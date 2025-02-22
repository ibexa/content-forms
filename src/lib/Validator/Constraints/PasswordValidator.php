<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Validator\Constraints;

use Ibexa\ContentForms\Validator\ValidationErrorsProcessor;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\User\PasswordValidationContext;
use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordValidator extends ConstraintValidator
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\UserService $userService
     */
    public function __construct(private UserService $userService)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function validate($value, Constraint $constraint): void
    {
        if (!is_string($value) || empty($value)) {
            return;
        }

        $passwordValidationContext = new PasswordValidationContext([
            'contentType' => $constraint->contentType,
        ]);

        $validationErrors = $this->userService->validatePassword($value, $passwordValidationContext);
        if (!empty($validationErrors)) {
            $validationErrorsProcessor = $this->createValidationErrorsProcessor();
            $validationErrorsProcessor->processValidationErrors($validationErrors);
        }
    }

    /**
     * @return \Ibexa\ContentForms\Validator\ValidationErrorsProcessor
     */
    protected function createValidationErrorsProcessor(): ValidationErrorsProcessor
    {
        return new ValidationErrorsProcessor($this->context);
    }
}
