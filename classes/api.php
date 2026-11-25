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
     * Message in Telegram HTML: bold subject, text and link, within the size limit.
     *
     * The first line of the text is dropped when it repeats the subject, and so are lines with only the link of the
     * notification, which stays at the end.
     *
     * @param string $subject Subject.
     * @param string $text Plain text, or Telegram HTML when $ishtml is true (see html() and markdown()).
     * @param string $url Link, or empty.
     * @param string $urlname Text of the link.
     * @param bool $ishtml Whether $text is already Telegram HTML.
     * @return string
     */
    public static function format(
        string $subject,
        string $text,
        string $url = '',
        string $urlname = '',
        bool $ishtml = false
    ): string {
        $subject = trim($subject);
        $head = '<b>' . s($subject) . '</b>';
        $tail = '';
        if ($url !== '' && clean_param($url, PARAM_URL) !== '') {
            $tail = "\n\n" . '<a href="' . s($url) . '">' . s($urlname !== '' ? $urlname : $url) . '</a>';
        } else {
            $url = '';
        }
        $html = trim($ishtml ? $text : s($text));
        $html = self::drop_repeated_lines($html, $subject, $url);
        $body = $html !== '' ? "\n\n" . $html : '';
        $room = self::MAX_LENGTH - \core_text::strlen($head . $tail);
        if (\core_text::strlen($body) > $room) {
            // Too long: fall back to plain text, cut before escaping, so that no tag or entity is broken.
            $plain = self::plain($html);
            $plainroom = max(0, $room - 10);
            do {
                $plain = \core_text::substr($plain, 0, $plainroom) . '…';
                $body = "\n\n" . s($plain);
                $plainroom -= 20;
            } while (\core_text::strlen($body) > $room && $plainroom > 0);
        }
        return $head . $body . $tail;
    }

    /**
     * Simple Markdown in Telegram HTML, after escaping the text.
     *
     * Supports *bold* or **bold**, _italic_ or __italic__, ~strike~, `code` and [text](https://...).
     * Underscores inside words (snake_case) are kept.
     *
     * @param string $text Text typed by a person.
     * @return string Telegram HTML.
     */
    public static function markdown(string $text): string {
        $html = s($text);
        // Code spans first: their content is not formatted.
        $codes = [];
        $html = preg_replace_callback("/\x60([^\x60\n]+)\x60/u", function (array $m) use (&$codes): string {
            $codes[] = '<code>' . $m[1] . '</code>';
            return "\x01" . (count($codes) - 1) . "\x02";
        }, $html);
        $html = preg_replace_callback('/\[([^\]\n]+)\]\(([^)\s]+)\)/u', function (array $m): string {
            $url = html_entity_decode($m[2], ENT_QUOTES, 'UTF-8');
            if (!preg_match('#^https?://#i', $url) || clean_param($url, PARAM_URL) === '') {
                return $m[0];
            }
            return '<a href="' . s($url) . '">' . $m[1] . '</a>';
        }, $html);
        $html = preg_replace('/\*\*(?=\S)(.+?)(?<=\S)\*\*/u', '<b>$1</b>', $html);
        $html = preg_replace('/(?<![\w*])\*(?=\S)([^*\n]+?)(?<=\S)\*(?![\w*])/u', '<b>$1</b>', $html);
        $html = preg_replace('/(?<!\w)__(?=\S)(.+?)(?<=\S)__(?!\w)/u', '<i>$1</i>', $html);
        $html = preg_replace('/(?<![\w_])_(?=\S)([^_\n]+?)(?<=\S)_(?![\w_])/u', '<i>$1</i>', $html);
        $html = preg_replace('/(?<![\w~])~(?=\S)([^~\n]+?)(?<=\S)~(?![\w~])/u', '<s>$1</s>', $html);
        return preg_replace_callback("/\x01(\d+)\x02/", fn(array $m): string => $codes[(int) $m[1]], $html);
    }

    /**
     * HTML of a Moodle notification in Telegram HTML.
     *
     * Keeps bold, italics, underline, strike, code, preformatted text, quotes and links (http and https); other tags
     * are removed, and paragraphs, line breaks and list items become new lines.
     *
     * @param string $html HTML of the notification.
     * @return string Telegram HTML.
     */
    public static function html(string $html): string {
        if (trim($html) === '') {
            return '';
        }
        $doc = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div>' . $html . '</div>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $root = $doc->getElementsByTagName('div')->item(0);
        $out = $root ? self::html_node($root) : '';
        $out = preg_replace("/[ \t]+\n/", "\n", $out);
        $out = preg_replace("/\n{3,}/", "\n\n", $out);
        return trim($out);
    }

    /**
     * Converts the children of an HTML node.
     *
     * @param \DOMNode $node Node.
     * @return string Telegram HTML.
     */
    private static function html_node(\DOMNode $node): string {
        $map = [
            'b' => 'b', 'strong' => 'b', 'i' => 'i', 'em' => 'i', 'u' => 'u', 'ins' => 'u',
            's' => 's', 'strike' => 's', 'del' => 's', 'code' => 'code', 'pre' => 'pre', 'blockquote' => 'blockquote',
        ];
        $out = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMText) {
                $out .= s(preg_replace('/\s+/u', ' ', $child->nodeValue));
                continue;
            }
            if (!$child instanceof \DOMElement) {
                continue;
            }
            $tag = strtolower($child->nodeName);
            if (in_array($tag, ['script', 'style', 'head', 'title'])) {
                continue;
            }
            $inner = $tag === 'pre' ? s($child->textContent) : self::html_node($child);
            if (isset($map[$tag])) {
                $out .= trim($inner) === '' ? $inner : '<' . $map[$tag] . '>' . $inner . '</' . $map[$tag] . '>';
            } else if ($tag === 'a') {
                $href = trim($child->getAttribute('href'));
                $ok = preg_match('#^https?://#i', $href) && clean_param($href, PARAM_URL) !== '';
                $out .= $ok && trim($inner) !== '' ? '<a href="' . s($href) . '">' . $inner . '</a>' : $inner;
            } else if ($tag === 'br') {
                $out .= "\n";
            } else if ($tag === 'li') {
                $out .= "\n• " . trim($inner);
            } else if (in_array($tag, ['p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'table', 'tr'])) {
                $out .= "\n" . trim($inner) . "\n";
            } else {
                $out .= $inner;
            }
        }
        return $out;
    }

    /**
     * Telegram HTML as plain text.
     *
     * @param string $html Telegram HTML.
     * @return string
     */
    private static function plain(string $html): string {
        return trim(html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Drops the first line when it repeats the subject, and the lines with only the link of the notification.
     *
     * @param string $html Telegram HTML.
     * @param string $subject Subject.
     * @param string $url Link of the notification, or empty.
     * @return string
     */
    private static function drop_repeated_lines(string $html, string $subject, string $url): string {
        $lines = explode("\n", $html);
        $first = true;
        $kept = [];
        foreach ($lines as $line) {
            $plain = self::plain($line);
            if ($first && $plain !== '') {
                $first = false;
                if ($plain === $subject) {
                    continue;
                }
            }
            if ($url !== '' && $plain === $url) {
                continue;
            }
            $kept[] = $line;
        }
        return trim(preg_replace("/\n{3,}/", "\n\n", implode("\n", $kept)));
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
     * @param string $text Text of the message, with simple Markdown (see markdown()).
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
        return self::send($chatid, self::format($subject, self::markdown($text), '', '', true));
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
