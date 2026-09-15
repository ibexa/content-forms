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

class UserUpdateFormProcessorTest extends TestCase
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
        $field = $this->getMockBuilder(FieldData::class)
            ->disableOriginalConstructor()
            ->getMock();
        $field->value = $value;
        $field->method('__get')
            ->with('fieldDefinition')
            ->willReturn(new FieldDefinition(['identifier' => $identifier, 'isTranslatable' => $isTranslatable]));

        return $field;
    }

    private function callSetContentFields(UserUpdateFormProcessor $processor, UserUpdateData $data, string $languageCode): void
    {
        $method = new ReflectionMethod(UserUpdateFormProcessor::class, 'setContentFields');
        $method->setAccessible(true);
        $method->invoke($processor, $data, $languageCode);
    }

    public function testNonTranslatableFieldIsSkippedOnNonMainLanguageUpdate(): void
    {
        $data = new UserUpdateData();
        $data->user = $this->createUser('eng-GB');
        $data->addFieldData($this->createField('title', true, 'translatable-value'));
        $data->addFieldData($this->createField('user_account', false, 'non-translatable-value'));

        $contentUpdateStruct = $this->getMockBuilder(ContentUpdateStruct::class)->getMock();
        $contentUpdateStruct->expects($this->once())
            ->method('setField')
            ->with('title', 'translatable-value', 'ger-DE');

        $contentService = $this->createMock(ContentService::class);
        $contentService->method('newContentUpdateStruct')->willReturn($contentUpdateStruct);

        $processor = new UserUpdateFormProcessor(
            $this->createMock(UserService::class),
            $contentService,
            $this->createMock(UrlGeneratorInterface::class)
        );

        $this->callSetContentFields($processor, $data, 'ger-DE');

        self::assertSame('ger-DE', $contentUpdateStruct->initialLanguageCode);
    }

    public function testAllFieldsAreUpdatedOnMainLanguageUpdate(): void
    {
        $data = new UserUpdateData();
        $data->user = $this->createUser('eng-GB');
        $data->addFieldData($this->createField('title', true, 'translatable-value'));
        $data->addFieldData($this->createField('user_account', false, 'non-translatable-value'));

        $contentUpdateStruct = $this->getMockBuilder(ContentUpdateStruct::class)->getMock();
        $contentUpdateStruct->expects($this->exactly(2))
            ->method('setField')
            ->withConsecutive(
                ['title', 'translatable-value', 'eng-GB'],
                ['user_account', 'non-translatable-value', 'eng-GB']
            );

        $contentService = $this->createMock(ContentService::class);
        $contentService->method('newContentUpdateStruct')->willReturn($contentUpdateStruct);

        $processor = new UserUpdateFormProcessor(
            $this->createMock(UserService::class),
            $contentService,
            $this->createMock(UrlGeneratorInterface::class)
        );

        $this->callSetContentFields($processor, $data, 'eng-GB');

        self::assertSame('eng-GB', $contentUpdateStruct->initialLanguageCode);
    }
}
