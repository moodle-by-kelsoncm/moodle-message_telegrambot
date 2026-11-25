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

namespace message_telegrambot\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/message/output/telegrambot/message_output_telegrambot.php');

/**
 * Form of the test message: chat id and text.
 *
 * @package    message_telegrambot
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class testmessage extends \moodleform {
    /**
     * Form fields.
     *
     * @return void
     */
    protected function definition(): void {
        $form = $this->_form;

        $form->addElement('text', 'chatid', get_string('chatid', 'message_telegrambot'), ['size' => 30]);
        $form->setType('chatid', PARAM_TEXT);
        $form->addRule('chatid', null, 'required', null, 'client');
        $form->addHelpButton('chatid', 'chatid', 'message_telegrambot');

        $form->addElement('textarea', 'text', get_string('testmessage_text', 'message_telegrambot'), ['rows' => 4, 'cols' => 60]);
        $form->setType('text', PARAM_TEXT);
        $form->setDefault('text', get_string('testmessage_default', 'message_telegrambot', format_string(get_site()->fullname)));
        $form->addRule('text', null, 'required', null, 'client');

        $this->add_action_buttons(false, get_string('testmessage_send', 'message_telegrambot'));
    }

    /**
     * Checks the chat id.
     *
     * @param array $data Submitted data.
     * @param array $files Submitted files.
     * @return array Errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        if (\message_output_telegrambot::clean_chatid((string) ($data['chatid'] ?? '')) === '') {
            $errors['chatid'] = get_string('invalidchatid', 'message_telegrambot');
        }
        if (trim((string) ($data['text'] ?? '')) === '') {
            $errors['text'] = get_string('required');
        }
        return $errors;
    }
}
