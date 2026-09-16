<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Form\Processor\User;

use Ibexa\ContentForms\Data\User\UserUpdateData;
use Ibexa\ContentForms\Form\Processor\User\UserUpdateFormProcessor;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentUpdateStruct;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\User\User;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserUpdateFormProcessorTest extends TestCase
{
    private function createUser(string $mainLanguageCode): User
    {
        $contentInfo = new ContentInfo(['id' => 42, 'mainLanguageCode' => $mainLanguageCode]);
        $versionInfo = new VersionInfo(['contentInfo' => $contentInfo]);
        $content = new Content(['versionInfo' => $versionInfo]);

        return new User(['content' => $content]);
    }

    private function createField(string $identifier, bool $isTranslatable, string $value): FieldData
    {
        $field = $this->createStub(FieldData::class);
        $field->value = $value;
        $field->method('__get')
            ->willReturn(new FieldDefinition(['identifier' => $identifier, 'isTranslatable' => $isTranslatable]));

        return $field;
    }

    private function callSetContentFields(UserUpdateFormProcessor $processor, UserUpdateData $data, string $languageCode): void
    {
        $method = new ReflectionMethod(UserUpdateFormProcessor::class, 'setContentFields');
        $method->setAccessible(true);
        $method->invoke($processor, $data, $languageCode);
    }

    private function createUserUpdateData(): UserUpdateData
    {
        $data = new UserUpdateData();
        $data->user = $this->createUser('eng-GB');
        $data->addFieldData($this->createField('title', true, 'translatable-value'));
        $data->addFieldData($this->createField('user_account', false, 'non-translatable-value'));

        return $data;
    }

    private function createProcessor(ContentUpdateStruct $contentUpdateStruct): UserUpdateFormProcessor
    {
        $contentService = $this->createMock(ContentService::class);
        $contentService->expects(self::once())
            ->method('newContentUpdateStruct')
            ->willReturn($contentUpdateStruct);

        return new UserUpdateFormProcessor(
            $this->createStub(UserService::class),
            $contentService,
            $this->createStub(UrlGeneratorInterface::class)
        );
    }

    /**
     * @dataProvider provideFieldUpdatesForLanguage
     *
     * @param list<array{0: string, 1: string, 2: string}> $expectedFieldUpdates
     */
    public function testSetContentFieldsUpdatesFieldsForLanguage(string $languageCode, array $expectedFieldUpdates): void
    {
        $data = $this->createUserUpdateData();

        $contentUpdateStruct = $this->getMockBuilder(ContentUpdateStruct::class)->getMock();
        $contentUpdateStruct
            ->expects(self::exactly(count($expectedFieldUpdates)))
            ->method('setField')
            ->withConsecutive(...$expectedFieldUpdates);

        $processor = $this->createProcessor($contentUpdateStruct);

        $this->callSetContentFields($processor, $data, $languageCode);

        self::assertSame($languageCode, $contentUpdateStruct->initialLanguageCode);
    }

    /**
     * @return iterable<string, array{0: string, 1: list<array{0: string, 1: string, 2: string}>}>
     */
    public function provideFieldUpdatesForLanguage(): iterable
    {
        yield 'non-translatable field is skipped on non-main language update' => [
            'ger-DE',
            [
                ['title', 'translatable-value', 'ger-DE'],
            ],
        ];

        yield 'all fields are updated on main language update' => [
            'eng-GB',
            [
                ['title', 'translatable-value', 'eng-GB'],
                ['user_account', 'non-translatable-value', 'eng-GB'],
            ],
        ];
    }
}
