@civic_settings
Feature: Check for custom fields on Civic theme page

  Ensure that the relevant fields are present.

  @api
  Scenario: The theme settings has all relevant custom fields
    Given I am logged in as a user with the "Site Administrator" role
    When I visit "/admin/appearance/settings/civic"
    Then the response status code should be 200

    And I see field "Header Mobile Logo"
    And I see field "Footer desktop logo"
    And I see field "Footer mobile logo"
    And I see field "Footer background image"
