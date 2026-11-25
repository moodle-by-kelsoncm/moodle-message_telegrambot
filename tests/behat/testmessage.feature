@message @message_telegrambot
Feature: Send a test message to Telegram
  In order to check the bot token and a chat id
  As an administrator
  I need to send a test message from the administration

  Scenario: The settings link to the test page, which warns when the token is missing
    Given I log in as "admin"
    And I navigate to "General > Messaging > Telegram bot" in site administration
    When I click on "Send a test message" "link"
    Then I should see "Send a Telegram test message"
    And I should see "The bot token is not configured yet."

  Scenario: An invalid chat id is refused
    Given I log in as "admin"
    And I navigate to "General > Messaging > Send a Telegram test message" in site administration
    When I set the field "Telegram chat id" to "not a chat"
    And I press "Send test message"
    Then I should see "Type a chat id: a number"
