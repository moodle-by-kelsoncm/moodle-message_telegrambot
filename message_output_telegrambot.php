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
 * Telegram bot message processor.
 *
 * @package    message_telegrambot
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/message/output/lib.php');

use message_telegrambot\api;

/**
 * Sends notifications to the Telegram chat chosen by each user (a person, a group or a channel).
 *
 * @package    message_telegrambot
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class message_output_telegrambot extends message_output {
    /** @var string User preference with the chat id. */
    public const PREFERENCE = 'message_processor_telegrambot_chatid';

    /**
     * Sends a notification.
     *
     * @param stdClass $eventdata Message.
     * @return bool True when sent or when the user has no chat; false when Telegram refused it.
     */
    public function send_message($eventdata) {
        if (!empty($eventdata->userto->suspended) || !empty($eventdata->userto->deleted)) {
            return true;
        }
        $chatid = trim((string) get_user_preferences(self::PREFERENCE, '', $eventdata->userto->id));
        if ($chatid === '' || !api::is_configured()) {
            return true;
        }
        $text = (string) ($eventdata->fullmessage ?? '');
        if ($text === '' && !empty($eventdata->fullmessagehtml)) {
            $text = html_to_text($eventdata->fullmessagehtml, 0, false);
        }
        if ($text === '') {
            $text = (string) ($eventdata->smallmessage ?? '');
        }
        $url = '';
        $urlname = '';
        if (!empty($eventdata->contexturl)) {
            $url = $eventdata->contexturl instanceof moodle_url ? $eventdata->contexturl->out(false)
                : (string) $eventdata->contexturl;
            $urlname = (string) ($eventdata->contexturlname ?? '');
        }
        $result = api::send($chatid, api::format((string) ($eventdata->subject ?? ''), $text, $url, $urlname));
        if (!$result['ok']) {
            debugging('message_telegrambot: ' . $result['error'], DEBUG_DEVELOPER);
        }
        return $result['ok'];
    }

    /**
     * Form fields of the user preferences.
     *
     * @param stdClass $preferences Preferences.
     * @return string
     */
    public function config_form($preferences) {
        global $OUTPUT;
        return $OUTPUT->render_from_template('message_telegrambot/preferences', [
            'chatid' => (string) ($preferences->telegrambot_chatid ?? ''),
            'configured' => api::is_configured(),
        ]);
    }

    /**
     * Saves the user preferences.
     *
     * @param stdClass $form Submitted data.
     * @param array $preferences Preferences to save.
     * @return void
     */
    public function process_form($form, &$preferences) {
        if (isset($form->telegrambot_chatid)) {
            $preferences[self::PREFERENCE] = self::clean_chatid((string) $form->telegrambot_chatid);
        }
    }

    /**
     * Loads the user preferences.
     *
     * @param stdClass $preferences Preferences.
     * @param int $userid User.
     * @return void
     */
    public function load_data(&$preferences, $userid) {
        $preferences->telegrambot_chatid = get_user_preferences(self::PREFERENCE, '', $userid);
    }

    /**
     * Whether the bot token is configured.
     *
     * @return bool
     */
    public function is_system_configured() {
        return api::is_configured();
    }

    /**
     * Whether the user has a chat.
     *
     * @param stdClass|null $user User, or the current one.
     * @return bool
     */
    public function is_user_configured($user = null) {
        global $USER;
        $user = $user ?? $USER;
        return trim((string) get_user_preferences(self::PREFERENCE, '', $user->id)) !== '';
    }

    /**
     * Off by default: each provider is enabled by the administrator.
     *
     * @return int
     */
    public function get_default_messaging_settings() {
        return MESSAGE_PERMITTED;
    }

    /**
     * Chat ids are integers (negative for groups) or @channel names.
     *
     * @param string $chatid Typed value.
     * @return string Clean value, or empty when invalid.
     */
    public static function clean_chatid(string $chatid): string {
        $chatid = trim($chatid);
        if (preg_match('/^-?\d{1,20}$/', $chatid) || preg_match('/^@[A-Za-z][A-Za-z0-9_]{4,31}$/', $chatid)) {
            return $chatid;
        }
        return '';
    }
}
