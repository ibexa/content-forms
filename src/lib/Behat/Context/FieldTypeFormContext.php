<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Behat\Context;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use PHPUnit\Framework\Assert as Assertion;

final class FieldTypeFormContext extends RawMinkContext implements SnippetAcceptingContext
{
    private static string $fieldIdentifier = 'field';

    /** @var array<string, string> */
    private static array $fieldTypeIdentifierMap = [
        'user' => 'ibexa_user',
        'textline' => 'ibexa_string',
        'selection' => 'ibexa_selection',
    ];

    private ContentTypeContext $contentTypeContext;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $environment = $scope->getEnvironment();
        $this->contentTypeContext = $environment->getContext(ContentTypeContext::class);
    }

    /**
     * @Given a content type with a(n) :fieldTypeIdentifier field definition
     * @Given a content type :contentTypeName with a(n) :fieldTypeIdentifier field definition
     */
    public function aContentTypeWithAGivenFieldDefinition($fieldTypeIdentifier, $contentTypeName = null): void
    {
        if (isset(self::$fieldTypeIdentifierMap[$fieldTypeIdentifier])) {
            $fieldTypeIdentifier = self::$fieldTypeIdentifierMap[$fieldTypeIdentifier];
        }

        $contentTypeCreateStruct = $this->contentTypeContext->newContentTypeCreateStruct();
        if (isset($contentTypeName)) {
            $contentTypeCreateStruct->names = ['eng-GB' => $contentTypeName];
        }

        $fieldDefinition = [
            'identifier' => self::$fieldIdentifier,
            'fieldTypeIdentifier' => $fieldTypeIdentifier,
            'names' => ['eng-GB' => 'Field'],
        ];
        if ($fieldTypeIdentifier === 'ibexa_selection') {
            $fieldDefinition = array_merge(
                $fieldDefinition,
                [
                    'fieldSettings' => ['isMultiple' => false,
                        'options' => [
                            0 => 'A first',
                            1 => 'Bielefeld',
                            2 => 'Test-value',
                            3 => 'Turtles',
                            4 => 'Zombies',
                        ],
                    ],
                ]
            );
        }

        $contentTypeCreateStruct->addFieldDefinition(new FieldDefinitionCreateStruct($fieldDefinition));
        $this->contentTypeContext->createContentType($contentTypeCreateStruct);
    }

    /**
     * @When /^I view the edit form for this field$/
     */
    public function iEditOrCreateContentOfThisType(): void
    {
        $this->visitPath(
            sprintf(
                '/content/create/nodraft/%s/eng-GB/2',
                $this->contentTypeContext->getCurrentContentType()->identifier
            )
        );
    }

    /**
     * @When /^I view the edit user form for this field$/
     */
    public function iEditOrCreateContentOfUserType(): void
    {
        $this->visitPath(
            sprintf(
                '/user/create/%s/eng-GB/2',
                $this->contentTypeContext->getCurrentContentType()->identifier
            )
        );
    }

    /**
     * @Then the edit form should contain an identifiable widget for :fieldTypeIdentifier field definition
     */
    public function theEditFormShouldContainAFieldsetNamedAfterTheFieldDefinition(string $fieldTypeIdentifier): void
    {
        $this->assertSession()->elementTextContains(
            'css',
            sprintf('form[name="ezplatform_content_forms_%s"] label', $this->getFieldTypeSelector($fieldTypeIdentifier)),
            'Field'
        );
    }

    /**
     * @Given it should contain a :type input field
     */
    public function itShouldContainAGivenTypeInputField($inputType): void
    {
        $this->assertSession()->elementExists(
            'css',
            sprintf(
                'form[name="ezplatform_content_forms_content_edit"] '
                . 'input[name="ezplatform_content_forms_content_edit[fieldsData][%s][value]"][type=%s]',
                self::$fieldIdentifier,
                $inputType
            )
        );
    }

    /**
     * @Given /^it should contain the following set of labels, and input fields of the following types:$/
     */
    public function itShouldContainTheFollowingSetOfLabelsAndInputFieldsTypes(TableNode $table): void
    {
        $inputNodeElements = $this->getSession()->getPage()->findAll(
            'css',
            sprintf(
                'form[name="ezplatform_content_forms_user_create"] #ezplatform_content_forms_user_create_fieldsData_%s_value input',
                self::$fieldIdentifier
            )
        );

        $actualInputFields = [];
        foreach ($inputNodeElements as $inputElement) {
            $type = $inputElement->getAttribute('type');
            $inputId = $inputElement->getAttribute('id');
            $label = $this->getSession()->getPage()->find('css', sprintf('label[for=%s]', $inputId))->getText();

            $actualInputFields[] = ['label' => $label, 'type' => $type];
        }

        foreach ($table->getColumnsHash() as $expectedField) {
            Assertion::assertContains($expectedField, $actualInputFields);
        }
    }

    /**
     * @Given /^the field definition is required$/
     * @Given /^the field definition is marked as required$/
     */
    public function theFieldDefinitionIsMarkedAsRequired(): void
    {
        $this->contentTypeContext->updateFieldDefinition(
            self::$fieldIdentifier,
            new FieldDefinitionUpdateStruct(['isRequired' => true])
        );
    }

    /**
     * @Then the value input fields for :fieldIdentifier field should be flagged as required
     */
    public function theInputFieldsShouldBeFlaggedAsRequired(string $fieldTypeIdentifier): void
    {
        $inputNodeElements = $this->getSession()->getPage()->findAll(
            'css',
            sprintf(
                'form[name="ezplatform_content_forms_%1$s"] #ezplatform_content_forms_%1$s_fieldsData_%2$s input',
                $this->getFieldTypeSelector($fieldTypeIdentifier),
                self::$fieldIdentifier
            )
        );

        Assertion::assertNotEmpty($inputNodeElements, 'The input field is not marked as required');

        $exceptions = $this->getRequiredFieldTypeExceptions($fieldTypeIdentifier);

        foreach ($inputNodeElements as $inputNodeElement) {
            $inputId = $inputNodeElement->getAttribute('id');
            $label = $this->getSession()->getPage()->find('css', sprintf('label[for=%s]', $inputId))->getText();

            $expectedState = array_key_exists($label, $exceptions) ? $exceptions[$label] : true;

            Assertion::assertEquals(
                $expectedState,
                $inputNodeElement->hasAttribute('required'),
                sprintf(
                    '%s input with id %s is not flagged as required',
                    $inputNodeElement->getAttribute('type'),
                    $inputNodeElement->getAttribute('id')
                )
            );
        }
    }

    public function setFieldDefinitionOption(string $option, mixed $value): void
    {
        $this->contentTypeContext->updateFieldDefinition(
            self::$fieldIdentifier,
            new FieldDefinitionUpdateStruct(['fieldSettings' => [$option => $value]])
        );
    }

    private function getFieldTypeSelector(string $fieldTypeIdentifier): string
    {
        if ($fieldTypeIdentifier === 'user') {
            return 'user_create';
        }

        return 'content_edit';
    }

    private function getRequiredFieldTypeExceptions(string $fieldTypeIdentifier): array
    {
        if ($fieldTypeIdentifier === 'user') {
            return ['Enabled' => false];
        }

        return [];
    }
}
