# Changelog

## 0.2.0 - 2026-11-25

- Administration page *Send a Telegram test message* (*Site administration > General > Messaging*), linked from
  the bot settings: sends a message to a chat id with the saved token and shows whether Telegram delivered it, or the
  error it returned without the token.
- The documentation points to the actual place of the settings (*General > Messaging*).

## 0.1.1 - 2026-11-25

- Credits of moodle-by-kelsoncm; first public release.

## 0.1.0 - 2026-11-25

- Message processor that delivers through the `sendMessage` method of the Telegram Bot API, with escaped HTML, the
  link of the notification and the 4096 characters limit.
- Bot token in the site settings, never shown in errors; chat id (user, group or channel) in the user preferences.
- Disabled by default for every notification type.
- Privacy: user preference and Telegram as an external location.
