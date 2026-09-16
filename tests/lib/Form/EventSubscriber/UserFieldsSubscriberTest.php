<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Form\EventSubscriber;

use Ibexa\ContentForms\Data\User\UserAccountFieldData;
use Ibexa\ContentForms\Data\User\UserUpdateData;
use Ibexa\ContentForms\Form\EventSubscriber\UserFieldsSubscriber;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Core\FieldType\User\Value as ApiUserValue;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\User\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigBuilder;
use Symfony\Component\Form\FormEvent;

final class UserFieldsSubscriberTest extends TestCase
{
    /**
     * @param list<Field> $fields
     */
    private function createUser(string $mainLanguageCode, array $fields): User
    {
        $contentInfo = new ContentInfo(['mainLanguageCode' => $mainLanguageCode]);
        $versionInfo = new VersionInfo(['contentInfo' => $contentInfo]);
        $content = new Content(['versionInfo' => $versionInfo, 'internalFields' => $fields]);

        return new User(['content' => $content]);
    }

    private function createForm(string $languageCode): Form
    {
        return new Form(
            (new FormConfigBuilder('form', null, new EventDispatcher(), [
                'languageCode' => $languageCode,
            ]))->getFormConfig()
        );
    }

    /**
     * @dataProvider provideUserAccountValueResolutionCases
     *
     * @param list<Field> $existingFields
     */
    public function testHandleUserAccountFieldResolvesBaseValueByLanguage(
        array $existingFields,
        string $mainLanguageCode,
        string $formLanguageCode,
        string $expectedBaseLogin
    ): void {
        $user = $this->createUser($mainLanguageCode, $existingFields);

        $data = new UserUpdateData();
        $data->user = $user;

        $fieldData = new FieldData([
            'field' => new Field(['fieldDefIdentifier' => 'user_account']),
            'fieldDefinition' => new FieldDefinition([
                'identifier' => 'user_account',
                'fieldTypeIdentifier' => 'ezuser',
            ]),
            'value' => new UserAccountFieldData('submitted-login', 'new-password', 'new@example.com', false),
        ]);
        $data->addFieldData($fieldData);

        $event = new FormEvent($this->createForm($formLanguageCode), $data);

        (new UserFieldsSubscriber())->handleUserAccountField($event);

        /** @var \Ibexa\Core\FieldType\User\Value $result */
        $result = $fieldData->value;

        self::assertSame($expectedBaseLogin, $result->login);
        self::assertSame('new@example.com', $result->email);
        self::assertFalse($result->enabled);
        self::assertSame('new-password', $result->plainPassword);
    }

    /**
     * @return iterable<string, array{0: list<Field>, 1: string, 2: string, 3: string}>
     */
    public function provideUserAccountValueResolutionCases(): iterable
    {
        yield 'falls back to main language field when translation has none' => [
            [
                new Field([
                    'fieldDefIdentifier' => 'user_account',
                    'languageCode' => 'eng-GB',
                    'value' => new ApiUserValue(['login' => 'main-login']),
                ]),
            ],
            'eng-GB',
            'ger-DE',
            'main-login',
        ];

        yield 'uses the field for the requested language directly when present' => [
            [
                new Field([
                    'fieldDefIdentifier' => 'user_account',
                    'languageCode' => 'eng-GB',
                    'value' => new ApiUserValue(['login' => 'main-login']),
                ]),
                new Field([
                    'fieldDefIdentifier' => 'user_account',
                    'languageCode' => 'ger-DE',
                    'value' => new ApiUserValue(['login' => 'translated-login']),
                ]),
            ],
            'eng-GB',
            'ger-DE',
            'translated-login',
        ];
    }
}
