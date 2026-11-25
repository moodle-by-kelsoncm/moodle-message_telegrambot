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

namespace message_telegrambot;

use GuzzleHttp\Handler\MockHandler;

/**
 * Telegram Bot API client: formats a notification and sends it with sendMessage.
 *
 * @package    message_telegrambot
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class api {
    /** @var int Maximum length of a Telegram message. */
    public const MAX_LENGTH = 4096;

    /** @var MockHandler|null HTTP answers used by the tests. */
    private static ?MockHandler $mock = null;

    /** @var array Requests sent while a mock is set. */
    private static array $history = [];

    /**
     * Sets the HTTP answers for the tests.
     *
     * @param MockHandler|null $mock Answers, or null to use the network.
     * @return void
     */
    public static function set_mock(?MockHandler $mock): void {
        self::$mock = $mock;
        self::$history = [];
    }

    /**
     * Requests sent while a mock was set.
     *
     * @return array
     */
    public static function get_history(): array {
        return self::$history;
    }

    /**
     * Whether the bot token is configured.
     *
     * @return bool
     */
    public static function is_configured(): bool {
        return trim((string) get_config('message_telegrambot', 'token')) !== '';
    }

    /**
     * Message in Telegram HTML: bold subject, text and link, escaped and within the size limit.
     *
     * @param string $subject Subject.
     * @param string $text Plain text.
     * @param string $url Link, or empty.
     * @param string $urlname Text of the link.
     * @return string
     */
    public static function format(string $subject, string $text, string $url = '', string $urlname = ''): string {
        $head = '<b>' . s(trim($subject)) . '</b>';
        $tail = '';
        if ($url !== '' && clean_param($url, PARAM_URL) !== '') {
            $tail = "\n\n" . '<a href="' . s($url) . '">' . s($urlname !== '' ? $urlname : $url) . '</a>';
        }
        $text = trim($text);
        $body = $text !== '' && $text !== trim($subject) ? "\n\n" . s($text) : '';
        $room = self::MAX_LENGTH - \core_text::strlen($head . $tail);
        if (\core_text::strlen($body) > $room) {
            // Cut the plain text before escaping, so that no entity is broken.
            $plainroom = max(0, $room - 10);
            do {
                $text = \core_text::substr($text, 0, $plainroom) . '…';
                $body = "\n\n" . s($text);
                $plainroom -= 20;
            } while (\core_text::strlen($body) > $room && $plainroom > 0);
        }
        return $head . $body . $tail;
    }

    /**
     * Sends a message to a chat.
     *
     * @param string $chatid Chat id (user, group or channel).
     * @param string $html Message in Telegram HTML.
     * @return array{ok: bool, error: string}
     */
    public static function send(string $chatid, string $html): array {
        $token = trim((string) get_config('message_telegrambot', 'token'));
        if ($token === '') {
            return ['ok' => false, 'error' => 'bot token not configured'];
        }
        $options = ['timeout' => 15, 'connect_timeout' => 10, 'http_errors' => false];
        if (self::$mock !== null) {
            $options['mock'] = self::$mock;
        }
        $payload = ['chat_id' => $chatid, 'text' => $html, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true];
        try {
            $client = new \core\http_client($options);
            $response = $client->request('POST', 'https://api.telegram.org/bot' . $token . '/sendMessage', [
                'json' => $payload,
            ]);
            if (self::$mock !== null) {
                self::$history[] = $payload;
            }
            $body = json_decode((string) $response->getBody(), true);
            if ($response->getStatusCode() === 200 && !empty($body['ok'])) {
                return ['ok' => true, 'error' => ''];
            }
            $description = is_array($body) ? (string) ($body['description'] ?? '') : '';
            return ['ok' => false, 'error' => 'HTTP ' . $response->getStatusCode() . ' ' . self::sanitise($description, $token)];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => self::sanitise($e->getMessage(), $token)];
        }
    }

    /**
     * Sends the test message of the administration page.
     *
     * @param string $chatid Chat id (user, group or channel), checked as in the user preferences.
     * @param string $text Plain text of the message.
     * @return array{ok: bool, error: string}
     */
    public static function send_test(string $chatid, string $text): array {
        global $CFG;
        require_once($CFG->dirroot . '/message/output/telegrambot/message_output_telegrambot.php');
        $chatid = \message_output_telegrambot::clean_chatid($chatid);
        if ($chatid === '') {
            return ['ok' => false, 'error' => get_string('invalidchatid', 'message_telegrambot')];
        }
        if (!self::is_configured()) {
            return ['ok' => false, 'error' => 'bot token not configured'];
        }
        $subject = get_string('testmessage_subject', 'message_telegrambot', format_string(get_site()->fullname));
        return self::send($chatid, self::format($subject, $text));
    }

    /**
     * Removes the token from an error message.
     *
     * @param string $message Message.
     * @param string $token Token.
     * @return string
     */
    public static function sanitise(string $message, string $token): string {
        return $token === '' ? $message : str_replace($token, '***', $message);
    }
}
