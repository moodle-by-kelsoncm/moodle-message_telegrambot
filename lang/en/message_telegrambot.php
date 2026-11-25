<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Strings of message_telegrambot (en).
 *
 * @package    message_telegrambot
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['chatid'] = 'Telegram chat id';
$string['chatid_help'] = 'Number of your chat with the bot, of a group (negative, such as -1001234567890) or the name of a channel (@name). Send a message to the bot or add it to the group, then ask the administrator for the id.';
$string['invalidchatid'] = 'Type a chat id: a number (negative for groups, such as -1001234567890) or a channel name (@name).';
$string['notconfigured'] = 'The Telegram bot is not configured on this site yet.';
$string['pluginname'] = 'Telegram bot';
$string['privacy:metadata:preference:chatid'] = 'The Telegram chat where the notifications of the user are sent.';
$string['privacy:metadata:telegram'] = 'Notifications are sent to Telegram through its Bot API.';
$string['privacy:metadata:telegram:chatid'] = 'The chat that receives the notification.';
$string['privacy:metadata:telegram:text'] = 'The subject, text and link of the notification.';
$string['testmessage'] = 'Send a Telegram test message';
$string['testmessage_default'] = 'Test message from {$a}. If you can read it, the Telegram bot of the site works.';
$string['testmessage_failed'] = 'Telegram did not deliver the message: {$a}';
$string['testmessage_intro'] = 'Sends a message to a Telegram chat with the token saved in the settings. Use it to check the token and the chat id before enabling the notifications. The bot must be in the group, or the person must have started a chat with it. The text accepts simple Markdown: *bold*, _italic_, ~strike~, code between backquotes and [text](https://...).';
$string['testmessage_link'] = '<a href="{$a}">Send a test message</a> to check the token and a chat id.';
$string['testmessage_notoken'] = 'The bot token is not configured yet. <a href="{$a}">Configure it</a> before sending a test message.';
$string['testmessage_send'] = 'Send test message';
$string['testmessage_sent'] = 'Message sent to the chat {$a}. Check it in Telegram.';
$string['testmessage_subject'] = 'Test message: {$a}';
$string['testmessage_text'] = 'Text';
$string['token'] = 'Bot token';
$string['token_desc'] = 'Token given by @BotFather when the bot is created. It is never shown in logs or errors.';
