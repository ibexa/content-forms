Feature: Checkbox field value edit form
    In order to edit content of a Checkbox field
    As an integrator
    I want the Checkbox field form to implement the FieldType's behaviour

Background:
    Given a content type with an ibexa_boolean field definition

Scenario: The attributes of the field have a form representation
    When I view the edit form for this field
    Then the edit form should contain an identifiable widget for ibexa_boolean field definition
     And it should contain a checkbox input field

Scenario: The input fields are flagged as required when the field definition is required
    Given the field definition is required
     When I view the edit form for this field
     Then the value input fields for ibexa_boolean field should be flagged as required
