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

namespace message_telegrambot\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\writer;

/**
 * Privacy provider: the chat id is a user preference, and the notifications are sent to Telegram.
 *
 * @package    message_telegrambot
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\user_preference_provider {
    /**
     * Data stored and sent.
     *
     * @param collection $collection Collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_user_preference('message_processor_telegrambot_chatid', 'privacy:metadata:preference:chatid');
        $collection->add_external_location_link('telegram', [
            'chatid' => 'privacy:metadata:telegram:chatid',
            'text' => 'privacy:metadata:telegram:text',
        ], 'privacy:metadata:telegram');
        return $collection;
    }

    /**
     * Exports the chat id of the user.
     *
     * @param int $userid User.
     * @return void
     */
    public static function export_user_preferences(int $userid) {
        $chatid = get_user_preferences('message_processor_telegrambot_chatid', null, $userid);
        if ($chatid !== null && $chatid !== '') {
            writer::export_user_preference(
                'message_telegrambot',
                'message_processor_telegrambot_chatid',
                $chatid,
                get_string('privacy:metadata:preference:chatid', 'message_telegrambot')
            );
        }
    }
}
