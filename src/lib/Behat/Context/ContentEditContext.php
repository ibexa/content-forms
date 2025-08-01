<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\MinkContext;
use Exception;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionCreateStruct;

final class ContentEditContext extends MinkContext implements Context, SnippetAcceptingContext
{
    /**
     * Name of the content that was created using the edit form. Used to validate that the content was created.
     */
    private ?string $createdContentName = null;

    private ContentTypeContext $contentTypeContext;

    /**
     * Identifier of the FieldDefinition used to cover validation.
     */
    private static string $constrainedFieldIdentifier = 'constrained_field';

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $environment = $scope->getEnvironment();

        $this->contentTypeContext = $environment->getContext(ContentTypeContext::class);
    }

    /**
     * @Then /^I should see a folder content edit form$/
     * @Then /^I should see a content edit form$/
     */
    public function iShouldSeeAContentEditForm(): void
    {
        $this->assertSession()->elementExists('css', 'form[name=ezplatform_content_forms_content_edit]');
    }

    /**
     * @Then /^I am on the View of the Content that was published$/
     */
    public function iAmOnTheViewOfTheContentThatWasPublished(): void
    {
        if (!isset($this->createdContentName)) {
            throw new Exception('No created content name set');
        }

        $this->assertElementOnPage('span.ibexa_string-field');
        $this->assertElementContainsText('span.ibexa_string-field', $this->createdContentName);
    }

    /**
     * @When /^I fill in the folder edit form$/
     */
    public function iFillInTheFolderEditForm(): void
    {
        // will only work for single value fields
        $this->createdContentName = 'Behat content edit @' . microtime(true);
        $this->fillField('ezplatform_content_forms_content_edit_fieldsData_name_value', $this->createdContentName);
    }

    /**
     * @Given /^that I have permission to create folders$/
     */
    public function thatIHavePermissionToCreateFolders(): void
    {
        $this->visit('/login');
        $this->fillField('_username', 'admin');
        $this->fillField('_password', 'publish');
        $this->getSession()->getPage()->find('css', 'form')->submit();
    }

    /**
     * @Given /^that I have permission to create content of this type$/
     */
    public function thatIHavePermissionToCreateContentOfThisType(): void
    {
        $this->thatIHavePermissionToCreateFolders();
    }

    /**
     * @When /^I go to the content creation page for this type$/
     */
    public function iGoToTheContentCreationPageForThisType(): void
    {
        $uri = sprintf(
            '/content/create/nodraft/%s/eng-GB/2',
            $this->contentTypeContext->getCurrentContentType()->identifier
        );

        $this->visit($uri);
    }

    /**
     * @Given /^I fill in the constrained field with an invalid value$/
     */
    public function iFillInTheConstrainedFieldWithAnInvalidValue(): void
    {
        $this->fillField(
            sprintf(
                'ezplatform_content_forms_content_edit_fieldsData_%s_value',
                self::$constrainedFieldIdentifier
            ),
            'abc'
        );

        if ($this->getSession()->getPage()->hasField('ezplatform_content_forms_content_edit_workflow_name')) {
            // in Enterprise Edition there are Workflow related form fields required
            $this->fillField('ezplatform_content_forms_content_edit_workflow_name', 'WorkfowName');
            $this->fillField('ezplatform_content_forms_content_edit_workflow_transition', 'WorkfowTransition');
            $this->fillField('ezplatform_content_forms_content_edit_workflow_comment', 'WorkfowComment');
            $this->fillField('ezplatform_content_forms_content_edit_workflow_reviewer', '14'); // "admin" user ID
        }
    }

    /**
     * @Then /^I am shown the content creation form$/
     */
    public function iAmShownTheContentCreationForm(): void
    {
        $uri = sprintf(
            '/content/create/nodraft/%s/eng-GB/2',
            $this->contentTypeContext->getCurrentContentType()->identifier
        );

        $this->assertPageAddress($uri);
        $this->assertElementOnPage(
            sprintf(
                'input[name="ezplatform_content_forms_content_edit[fieldsData][%s][value]"]',
                self::$constrainedFieldIdentifier
            )
        );
    }

    /**
     * @Given /^there is a relevant error message linked to the invalid field$/
     */
    public function thereIsARelevantErrorMessageLinkedToTheInvalidField(): void
    {
        $selector = sprintf(
            '#ezplatform_content_forms_content_edit_fieldsData_%s div ul li',
            self::$constrainedFieldIdentifier
        );

        $this->assertSession()->elementExists('css', $selector);
        $this->assertSession()->elementTextContains('css', $selector, 'The string cannot be shorter than 5 characters.');
    }

    /**
     * @Given /^that there is a content type with any kind of constraints on a Field Definition$/
     */
    public function thereIsAContentTypeWithAnyKindOfConstraintsOnAFieldDefinition(): void
    {
        $contentTypeCreateStruct = $this->contentTypeContext->newContentTypeCreateStruct();

        $contentTypeCreateStruct->addFieldDefinition(
            new FieldDefinitionCreateStruct(
                [
                    'identifier' => self::$constrainedFieldIdentifier,
                    'fieldTypeIdentifier' => 'ibexa_string',
                    'names' => ['eng-GB' => 'Field'],
                    'validatorConfiguration' => [
                        'StringLengthValidator' => ['minStringLength' => 5, 'maxStringLength' => 10],
                    ],
                ]
            )
        );

        $this->contentTypeContext->createContentType($contentTypeCreateStruct);
    }

    /**
     * @When /^a content creation form is displayed$/
     */
    public function aContentCreationFormIsDisplayed(): void
    {
        $this->visit('/content/create/nodraft/folder/eng-GB/2');
    }
}
