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
 * Settings of message_telegrambot.
 *
 * @package    message_telegrambot
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('messaging', new admin_externalpage(
        'message_telegrambot_testmessage',
        new lang_string('testmessage', 'message_telegrambot'),
        new moodle_url('/message/output/telegrambot/testmessage.php'),
        'moodle/site:config'
    ));
}

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'message_telegrambot/testmessagelink',
        '',
        new lang_string(
            'testmessage_link',
            'message_telegrambot',
            (new moodle_url('/message/output/telegrambot/testmessage.php'))->out(false)
        )
    ));
    $settings->add(new admin_setting_configpasswordunmask(
        'message_telegrambot/token',
        new lang_string('token', 'message_telegrambot'),
        new lang_string('token_desc', 'message_telegrambot'),
        ''
    ));
}
