<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Validator\Constraints;

use Ibexa\ContentForms\Validator\Constraints\Password;
use Ibexa\ContentForms\Validator\Constraints\PasswordValidator;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\User\PasswordValidationContext;
use Ibexa\Core\FieldType\ValidationError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class PasswordValidatorTest extends TestCase
{
    private UserService & MockObject $userService;

    private ExecutionContextInterface & MockObject $executionContext;

    private PasswordValidator $validator;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->executionContext = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new PasswordValidator($this->userService);
        $this->validator->initialize($this->executionContext);
    }

    #[DataProvider('dataProviderForValidateNotSupportedValueType')]
    public function testValidateShouldBeSkipped(mixed $value): void
    {
        $this->userService
            ->expects(self::never())
            ->method('validatePassword');

        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate($value, new Password());
    }

    public function testValid(): void
    {
        $password = 'pass';
        $contentType = self::createStub(ContentType::class);

        $this->userService
            ->expects(self::once())
            ->method('validatePassword')
            ->willReturnCallback(static function ($actualPassword, $actualContext) use ($password, $contentType): array {
                self::assertEquals($password, $actualPassword);
                self::assertInstanceOf(PasswordValidationContext::class, $actualContext);
                self::assertSame($contentType, $actualContext->contentType);

                return [];
            });

        $this->executionContext
            ->expects(self::never())
            ->method('buildViolation');

        $this->validator->validate($password, new Password(contentType: $contentType));
    }

    public function testInvalid(): void
    {
        $contentType = self::createStub(ContentType::class);
        $password = 'pass';
        $errorParameter = 'foo';
        $errorMessage = 'error';

        $this->userService
            ->expects(self::once())
            ->method('validatePassword')
            ->willReturnCallback(static function ($actualPassword, $actualContext) use ($password, $contentType, $errorMessage, $errorParameter): array {
                self::assertEquals($password, $actualPassword);
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

        $this->validator->validate('pass', new Password(contentType: $contentType));
    }

    public static function dataProviderForValidateNotSupportedValueType(): array
    {
        return [
            [new stdClass()],
            [null],
            [''],
        ];
    }
}
