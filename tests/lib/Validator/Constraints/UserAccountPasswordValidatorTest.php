<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Validator\Constraints;

use Ibexa\ContentForms\Data\User\UserAccountFieldData;
use Ibexa\ContentForms\Validator\Constraints\UserAccountPassword;
use Ibexa\ContentForms\Validator\Constraints\UserAccountPasswordValidator;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\User\PasswordValidationContext;
use Ibexa\Core\FieldType\ValidationError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class UserAccountPasswordValidatorTest extends TestCase
{
    private UserService & MockObject $userService;

    private ExecutionContextInterface & MockObject $executionContext;

    private UserAccountPasswordValidator $validator;

    #[DataProvider('dataProviderForValidateNotSupportedValueType')]
    public function testValidateShouldBeSkipped(mixed $value): void
    {
        $this->userService
            ->expects(self::never())
            ->method('validatePassword');

        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate($value, new UserAccountPassword());
    }

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->executionContext = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new UserAccountPasswordValidator($this->userService);
        $this->validator->initialize($this->executionContext);
    }

    public static function dataProviderForValidateNotSupportedValueType(): array
    {
        return [
            [new \stdClass()],
            [null],
            [''],
        ];
    }

    public function testValid(): void
    {
        $userAccount = new UserAccountFieldData('user', 'pass', 'user@ibexa.co');
        $contentType = self::createStub(ContentType::class);

        $this->userService
            ->expects(self::once())
            ->method('validatePassword')
            ->willReturnCallback(static function ($actualPassword, $actualContext) use ($userAccount, $contentType): array {
                self::assertEquals($userAccount->password, $actualPassword);
                self::assertInstanceOf(PasswordValidationContext::class, $actualContext);
                self::assertSame($contentType, $actualContext->contentType);

                return [];
            });

        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate($userAccount, new UserAccountPassword(contentType: $contentType));
    }

    public function testInvalid(): void
    {
        $contentType = self::createStub(ContentType::class);
        $userAccount = new UserAccountFieldData('user', 'pass', 'user@ibexa.co');
        $errorParameter = 'foo';
        $errorMessage = 'error';

        $this->userService
            ->expects(self::once())
            ->method('validatePassword')
            ->willReturnCallback(static function ($actualPassword, $actualContext) use ($userAccount, $contentType, $errorMessage, $errorParameter): array {
                self::assertEquals($userAccount->password, $actualPassword);
                self::assertInstanceOf(PasswordValidationContext::class, $actualContext);
                self::assertSame($contentType, $actualContext->contentType);

                return [
                    new ValidationError($errorMessage, null, ['%foo%' => $errorParameter]),
                ];
            });

        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->executionContext
            ->expects(self::once())
            ->method('buildViolation')
            ->willReturn($constraintViolationBuilder);
        $this->executionContext
            ->expects(self::once())
            ->method('buildViolation')
            ->with($errorMessage)
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder
            ->expects(self::once())
            ->method('setParameters')
            ->with(['%foo%' => $errorParameter])
            ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder
            ->expects(self::once())
            ->method('addViolation');

        $this->validator->validate($userAccount, new UserAccountPassword(contentType: $contentType));
    }
}
