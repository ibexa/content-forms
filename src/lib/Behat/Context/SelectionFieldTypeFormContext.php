<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Behat\Context;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\RawMinkContext;
use PHPUnit\Framework\Assert as Assertion;

final class SelectionFieldTypeFormContext extends RawMinkContext implements SnippetAcceptingContext
{
    private static string $fieldIdentifier = 'field';

    /**
     * @var \Ibexa\ContentForms\Behat\Context\FieldTypeFormContext
     */
    private $fieldTypeFormContext;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $this->fieldTypeFormContext = $scope->getEnvironment()->getContext(FieldTypeFormContext::class);
    }

    /**
     * @Given /^the field definition is set to single choice$/
     */
    public function setFieldDefinitionToSingleChoice(): void
    {
        $this->fieldTypeFormContext->setFieldDefinitionOption('isMultiple', false);
    }

    /**
     * @Given /^the field definition is set to multiple choice$/
     */
    public function setFieldDefinitionToMultipleChoice(): void
    {
        $this->fieldTypeFormContext->setFieldDefinitionOption('isMultiple', true);
    }

    /**
     * @Then it should contain a select field
     */
    public function itShouldContainASelectField(): void
    {
        $this->assertSession()->elementExists(
            'css',
            sprintf(
                'form[name="ezplatform_content_forms_content_edit"] '
                . 'select[name="ezplatform_content_forms_content_edit[fieldsData][%s][value]"]',
                self::$fieldIdentifier
            )
        );
    }

    /**
     * @Then the select field should be flagged as required
     */
    public function theSelectFieldShouldBeFlaggedAsRequired(): void
    {
        $nodeElements = $this->getSession()->getPage()->findAll(
            'css',
            sprintf(
                'select[name="ezplatform_content_forms_content_edit[fieldsData][%s][value][]"]',
                self::$fieldIdentifier
            )
        );
        Assertion::assertNotEmpty($nodeElements, 'The select field is not marked as required');
        foreach ($nodeElements as $nodeElement) {
            Assertion::assertEquals(
                'required',
                $nodeElement->getAttribute('required'),
                sprintf(
                    'The select with ID %s is not flagged as required',
                    $nodeElement->getAttribute('id')
                )
            );
        }
    }

    /**
     * @Then the input is a single selection dropdown
     */
    public function theInputIsASingleSelectionDropdown(): void
    {
        $selector = sprintf(
            'select[name="ezplatform_content_forms_content_edit[fieldsData][%s][value]"]',
            self::$fieldIdentifier
        );

        $this->assertSession()->elementExists('css', $selector);
        $this->assertSession()->elementNotContains('css', $selector, 'multiple="multiple"');
    }

    /**
     * @Then the input is a multiple selection dropdown
     */
    public function theInputIsAMultipleSelectionDropdown(): void
    {
        $selector = sprintf(
            'select[name="ezplatform_content_forms_content_edit[fieldsData][%s][value][]"][multiple=multiple]',
            self::$fieldIdentifier
        );

        $this->assertSession()->elementExists('css', $selector);
    }
}
