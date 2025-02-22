<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\RawMinkContext;
use Exception;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use Ibexa\Core\Repository\Values\User\UserReference;
use PHPUnit\Framework\Assert as Assertion;

final class ContentTypeContext extends RawMinkContext implements Context, SnippetAcceptingContext
{
    /**
     * Current content type within this context.
     */
    private ContentType $currentContentType;

    /**
     * Default Administrator user id.
     */
    private int $adminUserId = 14;

    public function __construct(
        private PermissionResolver $permissionResolver,
        private ContentTypeService $contentTypeService
    ) {
        $this->permissionResolver->setCurrentUserReference(new UserReference($this->adminUserId));
    }

    /**
     * @Given /^there is a content type "([^"]*)" with the id "([^"]*)"$/
     */
    public function thereIsAContentTypeWithId(string $contentTypeIdentifier, $id): void
    {
        try {
            $contentType = $this->contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
            Assertion::assertEquals($id, $contentType->id);
        } catch (NotFoundException $e) {
            Assertion::fail("No ContentType with the identifier '$contentTypeIdentifier' could be found.");
        }
    }

    /**
     * @Given I remove :fieldIdentifier field from :contentTypeIdentifier content type
     */
    public function iRemoveFieldFromContentType($fieldIdentifier, string $contentTypeIdentifier): void
    {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
        $contentTypeDraft = $this->contentTypeService->createContentTypeDraft($contentType);

        $fieldDefinition = $contentTypeDraft->getFieldDefinition($fieldIdentifier);
        $this->contentTypeService->removeFieldDefinition($contentTypeDraft, $fieldDefinition);

        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);
    }

    public function addFieldsTo(string $contentTypeIdentifier, array $fieldDefinitions): void
    {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
        $contentTypeDraft = $this->contentTypeService->createContentTypeDraft($contentType);

        foreach ($fieldDefinitions as $definition) {
            $this->contentTypeService->addFieldDefinition($contentTypeDraft, $definition);
        }

        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType
     *
     * @throws \Exception if no current content type has been defined in the context
     */
    public function getCurrentContentType(): ContentType
    {
        if ($this->currentContentType === null) {
            throw new Exception('No current content type has been defined in the context');
        }

        return $this->currentContentType;
    }

    public function createContentType(ContentTypeCreateStruct $struct): void
    {
        if (!isset($struct->mainLanguageCode)) {
            $struct->mainLanguageCode = 'eng-GB';
        }

        if (!isset($struct->names)) {
            $struct->names = ['eng-GB' => $struct->identifier];
        }

        $this->contentTypeService->publishContentTypeDraft(
            $this->contentTypeService->createContentType(
                $struct,
                [$this->contentTypeService->loadContentTypeGroupByIdentifier('Content')]
            )
        );

        $this->currentContentType = $this->contentTypeService->loadContentTypeByIdentifier($struct->identifier);
    }

    /**
     * Creates a new content type create struct. If the identifier is not specified, a custom one is given.
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeCreateStruct
     */
    public function newContentTypeCreateStruct($identifier = null): ContentTypeCreateStruct
    {
        return $this->contentTypeService->newContentTypeCreateStruct(
            $identifier ?: $identifier = str_replace('.', '', uniqid('content_type_', true))
        );
    }

    public function updateFieldDefinition($identifier, FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct): void
    {
        $contentTypeDraft = $this->contentTypeService->createContentTypeDraft($this->currentContentType);

        $this->contentTypeService->updateFieldDefinition(
            $contentTypeDraft,
            $this->currentContentType->getFieldDefinition($identifier),
            $fieldDefinitionUpdateStruct
        );

        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);

        $this->currentContentType = $this->contentTypeService->loadContentTypeByIdentifier(
            $this->currentContentType->identifier
        );
    }
}
