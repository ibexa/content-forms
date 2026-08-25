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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class UserAccountFieldValueFormMapperTransformerTest extends TestCase
{
    public function testMapFieldValueFormDisablesNonTranslatableFieldOnNonMainLanguageTranslation(): void
    {
        $mapper = new UserAccountFieldValueFormMapper();

        $fieldDefinition = new FieldDefinition([
            'names' => [],
            'isTranslatable' => false,
            'defaultValue' => new ApiUserValue(),
        ]);

        $data = $this->getMockBuilder(FieldData::class)
            ->disableOriginalConstructor()
            ->getMock();
        $data->expects($this->once())
            ->method('__get')
            ->with('fieldDefinition')
            ->willReturn($fieldDefinition);

        $config = $this->getMockBuilder(FormConfigInterface::class)->getMock();
        $config->method('getOption')
            ->willReturnMap([
                ['languageCode', null, 'ger-DE'],
                ['mainLanguageCode', null, 'eng-GB'],
            ]);

        $formFactory = $this->getMockBuilder(FormFactoryInterface::class)
            ->setMethods(['addModelTransformer', 'setAutoInitialize', 'getForm'])
            ->getMockForAbstractClass();
        $formFactory->method('createBuilder')->willReturn($formFactory);
        $formFactory->method('addModelTransformer')->willReturn($formFactory);
        $formFactory->method('setAutoInitialize')->willReturn($formFactory);
        $formFactory->expects($this->once())
            ->method('create')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(static function (array $options): bool {
                    return true === $options['disabled'];
                })
            )
            ->willReturn($formFactory);

        $config->method('getFormFactory')->willReturn($formFactory);

        $fieldForm = $this->getMockBuilder(FormInterface::class)->getMock();
        $fieldForm->method('getConfig')->willReturn($config);

        $rootData = new ContentTranslationData();
        $formRoot = $this->getMockBuilder(FormInterface::class)->getMock();
        $formRoot->method('getData')->willReturn($rootData);
        $rootConfig = $this->getMockBuilder(FormConfigInterface::class)->getMock();
        $rootConfig->method('getOption')->with('intent')->willReturn('update');
        $formRoot->method('getConfig')->willReturn($rootConfig);

        $userEditForm = $this->getMockBuilder(FormInterface::class)->getMock();
        $userEditForm->method('getRoot')->willReturn($formRoot);
        $fieldForm->method('getRoot')->willReturn($userEditForm);

        $mapper->mapFieldValueForm($fieldForm, $data);
    }

    public function testModelTransformerForTranslationClonesLastTransformedValueOnReverseTransform(): void
    {
        $mapper = new UserAccountFieldValueFormMapper();

        $defaultValue = new ApiUserValue(['login' => 'default-login', 'email' => 'default@example.com']);
        $fieldDefinition = new FieldDefinition(['names' => [], 'defaultValue' => $defaultValue]);

        $transformer = $mapper->getModelTransformerForTranslation($fieldDefinition);

        $currentValue = new ApiUserValue([
            'login' => 'current-login',
            'email' => 'current@example.com',
            'enabled' => true,
            'maxLogin' => 5,
        ]);

        $transformer->transform($currentValue);

        $submittedData = new UserAccountFieldData('new-login', '', 'new@example.com', false);
        $result = $transformer->reverseTransform($submittedData);

        self::assertNotSame($currentValue, $result);
        self::assertSame('new-login', $result->login);
        self::assertSame('new@example.com', $result->email);
        self::assertFalse($result->enabled);
        self::assertSame(5, $result->maxLogin);
    }

    public function testModelTransformerForTranslationFallsBackToFieldDefinitionDefaultValue(): void
    {
        $mapper = new UserAccountFieldValueFormMapper();

        $defaultValue = new ApiUserValue(['login' => 'default-login', 'maxLogin' => 3]);
        $fieldDefinition = new FieldDefinition(['names' => [], 'defaultValue' => $defaultValue]);

        $transformer = $mapper->getModelTransformerForTranslation($fieldDefinition);

        $submittedData = new UserAccountFieldData('new-login', '', 'new@example.com', true);
        $result = $transformer->reverseTransform($submittedData);

        self::assertNotSame($defaultValue, $result);
        self::assertSame('new-login', $result->login);
        self::assertSame('new@example.com', $result->email);
        self::assertTrue($result->enabled);
        self::assertSame(3, $result->maxLogin);
    }
}
