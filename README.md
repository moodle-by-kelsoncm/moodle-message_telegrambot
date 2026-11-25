<img src="docs/logo/logo.svg" alt="moodle-message_telegrambot logo" width="96" align="right" />

# moodle-message_telegrambot

[![Moodle Plugin CI](https://github.com/moodle-by-kelsoncm/moodle-message_telegrambot/actions/workflows/ci.yml/badge.svg)](https://github.com/moodle-by-kelsoncm/moodle-message_telegrambot/actions/workflows/ci.yml)
[![Documentation](https://github.com/moodle-by-kelsoncm/moodle-message_telegrambot/actions/workflows/docs.yml/badge.svg)](https://github.com/moodle-by-kelsoncm/moodle-message_telegrambot/actions/workflows/docs.yml)

Telegram bot message processor for Moodle (`message_telegrambot`). Delivers Moodle notifications to the Telegram chat chosen by each user: the chat with the bot, a group or a channel. It is a new component: the old `message_telegram` of the Plugins directory (2017, Moodle 3.2 and 3.3) can stay installed.

**Status:** 0.3.1 (alpha). Download it from the [releases](https://github.com/moodle-by-kelsoncm/moodle-message_telegrambot/releases).

Documentation (English and Brazilian Portuguese): https://moodle-by-kelsoncm.github.io/moodle-message_telegrambot/

Requirements: Moodle 4.5 or 5.3 (LTS).

Quick start: install the plugin in `message/output/telegrambot` (`public/message/output/telegrambot` on Moodle 5.1 and later) and upgrade Moodle. Then set the bot token in *Site administration > General > Messaging > Telegram bot*, and send a test message from the link on that page.

Part of [moodle-by-kelsoncm](https://github.com/moodle-by-kelsoncm).

License: [GPL-3.0](LICENSE).
