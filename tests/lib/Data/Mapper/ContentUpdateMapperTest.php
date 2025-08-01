<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Data\Mapper;

use Ibexa\ContentForms\Data\Mapper\ContentUpdateMapper;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Field as APIField;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location as APILocation;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use PHPUnit\Framework\TestCase;

final class ContentUpdateMapperTest extends TestCase
{
    public function testMapToFormData(): void
    {
        $currentFields = [
            new APIField([
                'fieldDefIdentifier' => 'name',
                'fieldTypeIdentifier' => 'ibexa_string',
                'languageCode' => 'eng-GB',
                'value' => 'Name',
            ]),
            new APIField([
                'fieldDefIdentifier' => 'short_name',
                'fieldTypeIdentifier' => 'ibexa_string',
                'languageCode' => 'eng-DE',
                'value' => $expectedShortName = 'Nontranslateable short name',
            ]),
        ];

        $newName = 'GER name';
        $newShortName = '';
        $content = $this->getContent($newName, $newShortName, 'ger-DE');

        $data = (new ContentUpdateMapper())->mapToFormData($content, [
            'languageCode' => 'ger-DE',
            'contentType' => $this->getContentType(),
            'currentFields' => $currentFields,
        ]);

        $fieldsData = $data->getFieldsData();

        self::assertSame($newName, $fieldsData['name']->value);
        self::assertSame($expectedShortName, $fieldsData['short_name']->value);
    }

    public function testMapToFormDataWithTheSameLanguage(): void
    {
        $currentFields = [
            new APIField([
                'fieldDefIdentifier' => 'name',
                'fieldTypeIdentifier' => 'ibexa_string',
                'languageCode' => 'eng-GB',
                'value' => 'Name',
            ]),
            new APIField([
                'fieldDefIdentifier' => 'short_name',
                'fieldTypeIdentifier' => 'ibexa_string',
                'languageCode' => 'eng-GB',
                'value' => 'Short name',
            ]),
        ];

        $newName = 'New name';
        $newShortName = 'New short name';
        $content = $this->getContent($newName, $newShortName, 'eng-GB');

        $data = (new ContentUpdateMapper())->mapToFormData($content, [
            'languageCode' => 'eng-GB',
            'contentType' => $this->getContentType(),
            'currentFields' => $currentFields,
        ]);

        $fieldsData = $data->getFieldsData();

        self::assertSame($newName, $fieldsData['name']->getValue());
        self::assertSame($newShortName, $fieldsData['short_name']->getValue());
    }

    private function getContent(string $name, string $shortName, string $languageCode): Content
    {
        return new Content([
            'versionInfo' => new VersionInfo([
                'contentInfo' => new ContentInfo([
                    'remoteId' => 'foo',
                    'mainLanguage' => new Language([
                        'languageCode' => 'eng-GB',
                    ]),
                    'alwaysAvailable' => true,
                    'sectionId' => 2,
                    'section' => new Section([
                        'id' => 2,
                        'identifier' => 'foo_section_identifier',
                    ]),
                    'publishedDate' => new \DateTime('2020-10-30T11:40:09+00:00'),
                    'modificationDate' => new \DateTime('2020-10-30T11:40:09+00:00'),
                    'mainLocation' => new APILocation([
                        'parentLocationId' => 1,
                        'hidden' => true,
                        'sortField' => 9,
                        'sortOrder' => 0,
                        'priority' => 1,
                    ]),
                ]),
            ]),
            'contentType' => $this->getContentType(),
            'internalFields' => [
                new APIField([
                    'fieldDefIdentifier' => 'name',
                    'fieldTypeIdentifier' => 'ibexa_string',
                    'languageCode' => $languageCode,
                    'value' => $name,
                ]),
                new APIField([
                    'fieldDefIdentifier' => 'short_name',
                    'fieldTypeIdentifier' => 'ibexa_string',
                    'languageCode' => $languageCode,
                    'value' => $shortName,
                ]),
            ],
        ]);
    }

    private function getContentType(): ContentType
    {
        return new ContentType([
            'identifier' => 'folder',
            'fieldDefinitions' => new FieldDefinitionCollection([
                new FieldDefinition([
                    'identifier' => 'name',
                    'isTranslatable' => true,
                    'defaultValue' => '',
                ]),
                new FieldDefinition([
                    'identifier' => 'short_name',
                    'isTranslatable' => false,
                    'defaultValue' => '',
                ]),
            ]),
        ]);
    }
}
