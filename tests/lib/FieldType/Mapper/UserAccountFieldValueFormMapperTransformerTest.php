<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\FieldType\Mapper;

use Ibexa\ContentForms\Data\ContentTranslationData;
use Ibexa\ContentForms\Data\User\UserAccountFieldData;
use Ibexa\ContentForms\FieldType\Mapper\UserAccountFieldValueFormMapper;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Core\FieldType\User\Value as ApiUserValue;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

final class UserAccountFieldValueFormMapperTransformerTest extends TestCase
{
    public function testMapFieldValueFormDisablesNonTranslatableFieldOnNonMainLanguageTranslation(): void
    {
        $mapper = new UserAccountFieldValueFormMapper();

        $fieldDefinition = new FieldDefinition([
            'names' => [],
            'isTranslatable' => false,
            'defaultValue' => new ApiUserValue(),
        ]);

        $data = $this->createMock(FieldData::class);
        $data->expects($this->once())
            ->method('getFieldDefinition')
            ->willReturn($fieldDefinition);

        $config = self::createStub(FormConfigInterface::class);
        $config->method('getOption')
            ->willReturnMap([
                ['languageCode', null, 'ger-DE'],
                ['mainLanguageCode', null, 'eng-GB'],
            ]);

        $builtForm = self::createStub(FormInterface::class);

        $valueBuilder = $this->createMock(FormBuilderInterface::class);
        $valueBuilder->expects($this->once())
            ->method('addModelTransformer')
            ->with(self::isInstanceOf(CallbackTransformer::class))
            ->willReturnSelf();
        $valueBuilder->expects($this->once())
            ->method('setAutoInitialize')
            ->with(false)
            ->willReturnSelf();
        $valueBuilder->expects($this->once())
            ->method('getForm')
            ->willReturn($builtForm);

        $rootBuilder = $this->createMock(FormBuilderInterface::class);
        $rootBuilder->expects($this->once())
            ->method('create')
            ->with(
                self::anything(),
                self::anything(),
                self::callback(static function (array $options): bool {
                    return true === $options['disabled'] && 'update' === $options['intent'];
                })
            )
            ->willReturn($valueBuilder);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->method('createBuilder')->willReturn($rootBuilder);

        $config->method('getFormFactory')->willReturn($formFactory);

        $fieldForm = $this->createMock(FormInterface::class);
        $fieldForm->method('getConfig')->willReturn($config);
        $fieldForm->expects($this->once())
            ->method('add')
            ->with($builtForm);

        $rootData = new ContentTranslationData();
        $formRoot = $this->createMock(FormInterface::class);
        $formRoot->expects($this->once())
            ->method('getData')
            ->willReturn($rootData);
        $rootConfig = $this->createMock(FormConfigInterface::class);
        $rootConfig->expects($this->once())
            ->method('getOption')
            ->with('intent')
            ->willReturn('update');
        $formRoot->method('getConfig')->willReturn($rootConfig);

        $userEditForm = self::createStub(FormInterface::class);
        $userEditForm->method('getRoot')->willReturn($formRoot);
        $fieldForm->method('getRoot')->willReturn($userEditForm);

        $mapper->mapFieldValueForm($fieldForm, $data);
    }

    /**
     * @param array{login: string, email: string, enabled: bool, maxLogin: int} $expected
     */
    #[DataProvider('provideReverseTransformCases')]
    public function testModelTransformerForTranslationReverseTransform(
        ApiUserValue $defaultValue,
        ?ApiUserValue $currentValue,
        UserAccountFieldData $submittedData,
        ApiUserValue $baseValueForComparison,
        array $expected
    ): void {
        $mapper = new UserAccountFieldValueFormMapper();
        $fieldDefinition = new FieldDefinition(['names' => [], 'defaultValue' => $defaultValue]);

        $transformer = $mapper->getModelTransformerForTranslation($fieldDefinition);

        if (null !== $currentValue) {
            $transformer->transform($currentValue);
        }

        $result = $transformer->reverseTransform($submittedData);

        self::assertNotSame($baseValueForComparison, $result);
        self::assertSame($expected['login'], $result->login);
        self::assertSame($expected['email'], $result->email);
        self::assertSame($expected['enabled'], $result->enabled);
        self::assertSame($expected['maxLogin'], $result->maxLogin);
    }

    /**
     * @return iterable<string, array{
     *     0: ApiUserValue,
     *     1: ApiUserValue|null,
     *     2: UserAccountFieldData,
     *     3: ApiUserValue,
     *     4: array{login: string, email: string, enabled: bool, maxLogin: int}
     * }>
     */
    public static function provideReverseTransformCases(): iterable
    {
        $currentValue = new ApiUserValue([
            'login' => 'current-login',
            'email' => 'current@example.com',
            'enabled' => true,
            'maxLogin' => 5,
        ]);

        yield 'clones last transformed value on reverse transform' => [
            new ApiUserValue(['login' => 'default-login', 'email' => 'default@example.com']),
            $currentValue,
            new UserAccountFieldData('new-login', '', 'new@example.com', false),
            $currentValue,
            [
                'login' => 'new-login',
                'email' => 'new@example.com',
                'enabled' => false,
                'maxLogin' => 5,
            ],
        ];

        $defaultValue = new ApiUserValue(['login' => 'default-login', 'maxLogin' => 3]);

        yield 'falls back to field definition default value' => [
            $defaultValue,
            null,
            new UserAccountFieldData('new-login', '', 'new@example.com', true),
            $defaultValue,
            [
                'login' => 'new-login',
                'email' => 'new@example.com',
                'enabled' => true,
                'maxLogin' => 3,
            ],
        ];
    }
}
