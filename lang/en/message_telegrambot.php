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
$string['notconfigured'] = 'The Telegram bot is not configured on this site yet.';
$string['pluginname'] = 'Telegram bot';
$string['privacy:metadata:preference:chatid'] = 'The Telegram chat where the notifications of the user are sent.';
$string['privacy:metadata:telegram'] = 'Notifications are sent to Telegram through its Bot API.';
$string['privacy:metadata:telegram:chatid'] = 'The chat that receives the notification.';
$string['privacy:metadata:telegram:text'] = 'The subject, text and link of the notification.';
$string['token'] = 'Bot token';
$string['token_desc'] = 'Token given by @BotFather when the bot is created. It is never shown in logs or errors.';
