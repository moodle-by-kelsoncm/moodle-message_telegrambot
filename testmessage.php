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
 * Sends a test message to a Telegram chat, to check the bot token and the chat id.
 *
 * @package    message_telegrambot
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('message_telegrambot_testmessage');

$url = new moodle_url('/message/output/telegrambot/testmessage.php');
$form = new \message_telegrambot\form\testmessage($url);

$result = null;
if ($data = $form->get_data()) {
    $result = \message_telegrambot\api::send_test((string) $data->chatid, (string) $data->text);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('testmessage', 'message_telegrambot'));

if (!\message_telegrambot\api::is_configured()) {
    $settings = new moodle_url('/admin/settings.php', ['section' => 'messagesettingtelegrambot']);
    echo $OUTPUT->notification(
        get_string('testmessage_notoken', 'message_telegrambot', $settings->out(false)),
        \core\output\notification::NOTIFY_WARNING
    );
}
if ($result !== null) {
    if ($result['ok']) {
        echo $OUTPUT->notification(
            get_string('testmessage_sent', 'message_telegrambot', s($data->chatid)),
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        echo $OUTPUT->notification(
            get_string('testmessage_failed', 'message_telegrambot', s($result['error'])),
            \core\output\notification::NOTIFY_ERROR
        );
    }
}

echo html_writer::tag('p', get_string('testmessage_intro', 'message_telegrambot'));
$form->display();

echo $OUTPUT->footer();
