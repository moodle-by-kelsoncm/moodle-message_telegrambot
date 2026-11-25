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
use GuzzleHttp\Psr7\Response;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/message/output/telegrambot/message_output_telegrambot.php');

/**
 * Tests of the Telegram bot processor.
 *
 * @package    message_telegrambot
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \message_telegrambot\api
 * @covers     \message_output_telegrambot
 */
final class telegrambot_test extends \advanced_testcase {
    /**
     * Prepares each test.
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        set_config('curlsecurityblockedhosts', '');
        set_config('curlsecurityallowedport', '');
        set_config('token', '123456:SECRET-TOKEN', 'message_telegrambot');
    }

    /**
     * Cleans the mock.
     *
     * @return void
     */
    protected function tearDown(): void {
        api::set_mock(null);
        parent::tearDown();
    }

    /**
     * A notification to a user.
     *
     * @param \stdClass $user Recipient.
     * @return \stdClass
     */
    private function eventdata(\stdClass $user): \stdClass {
        return (object) [
            'userto' => $user,
            'subject' => '[Warning] Video <private>',
            'fullmessage' => "Course A & B\nOnly 1% left",
            'contexturl' => 'https://example.com/report?id=1&x=2',
            'contexturlname' => 'Open',
        ];
    }

    /**
     * The message is HTML escaped and keeps subject, text and link.
     *
     * @return void
     */
    public function test_format(): void {
        $html = api::format('A <b> & c', "line 1\nline <2>", 'https://example.com/?a=1&b=2', 'Open');
        $this->assertStringStartsWith('<b>A &lt;b&gt; &amp; c</b>', $html);
        $this->assertStringContainsString("line 1\nline &lt;2&gt;", $html);
        $this->assertStringContainsString('<a href="https://example.com/?a=1&amp;b=2">Open</a>', $html);
        // The text is cut to fit the Telegram limit, keeping the subject and the link.
        $long = api::format('Subject', str_repeat('é & ', 3000), 'https://example.com/', 'Open');
        $this->assertLessThanOrEqual(api::MAX_LENGTH, \core_text::strlen($long));
        $this->assertStringEndsWith('</a>', $long);
        $this->assertStringNotContainsString('&am…', $long);
    }

    /**
     * The notification goes to the chat of the user through sendMessage.
     *
     * @return void
     */
    public function test_send_message(): void {
        $user = $this->getDataGenerator()->create_user();
        set_user_preference(\message_output_telegrambot::PREFERENCE, '-1001234567890', $user);
        api::set_mock(new MockHandler([new Response(200, [], '{"ok":true,"result":{}}')]));

        $this->assertTrue((new \message_output_telegrambot())->send_message($this->eventdata($user)));
        $history = api::get_history();
        $this->assertCount(1, $history);
        $this->assertSame('-1001234567890', $history[0]['chat_id']);
        $this->assertSame('HTML', $history[0]['parse_mode']);
        $this->assertStringContainsString('Video &lt;private&gt;', $history[0]['text']);
        $this->assertStringContainsString('Course A &amp; B', $history[0]['text']);
    }

    /**
     * A refusal of Telegram returns false and never shows the token.
     *
     * @return void
     */
    public function test_send_message_refused(): void {
        $user = $this->getDataGenerator()->create_user();
        set_user_preference(\message_output_telegrambot::PREFERENCE, '42', $user);
        api::set_mock(new MockHandler([new Response(400, [], '{"ok":false,"description":"Bad Request: chat not found"}')]));

        $this->assertFalse((new \message_output_telegrambot())->send_message($this->eventdata($user)));
        $this->assertDebuggingCalled();

        api::set_mock(new MockHandler([new Response(401, [], '{"ok":false,"description":"Unauthorized 123456:SECRET-TOKEN"}')]));
        $result = api::send('42', 'x');
        $this->assertFalse($result['ok']);
        $this->assertStringNotContainsString('SECRET-TOKEN', $result['error']);
    }

    /**
     * Users without a chat, and sites without a token, send nothing.
     *
     * @return void
     */
    public function test_not_configured(): void {
        $user = $this->getDataGenerator()->create_user();
        $processor = new \message_output_telegrambot();
        api::set_mock(new MockHandler([]));
        $this->assertFalse($processor->is_user_configured($user));
        $this->assertTrue($processor->send_message($this->eventdata($user)));
        $this->assertCount(0, api::get_history());

        set_config('token', '', 'message_telegrambot');
        $this->assertFalse($processor->is_system_configured());
        $this->assertFalse(api::send('42', 'x')['ok']);
    }

    /**
     * Chat ids accepted in the preferences.
     *
     * @return void
     */
    public function test_preferences(): void {
        $this->assertSame('-1001234567890', \message_output_telegrambot::clean_chatid(' -1001234567890 '));
        $this->assertSame('123456', \message_output_telegrambot::clean_chatid('123456'));
        $this->assertSame('@team_alerts', \message_output_telegrambot::clean_chatid('@team_alerts'));
        $this->assertSame('', \message_output_telegrambot::clean_chatid('12a'));
        $this->assertSame('', \message_output_telegrambot::clean_chatid('@x'));

        $user = $this->getDataGenerator()->create_user();
        $processor = new \message_output_telegrambot();
        $preferences = [];
        $processor->process_form((object) ['telegrambot_chatid' => '-100999'], $preferences);
        $this->assertSame(['message_processor_telegrambot_chatid' => '-100999'], $preferences);
        set_user_preferences($preferences, $user->id);
        $loaded = new \stdClass();
        $processor->load_data($loaded, $user->id);
        $this->assertSame('-100999', $loaded->telegrambot_chatid);
        $this->assertTrue($processor->is_user_configured($user));
    }

    /**
     * The test message of the administration page goes to the typed chat, with the site name in the subject.
     *
     * @return void
     */
    public function test_send_test(): void {
        api::set_mock(new MockHandler([new Response(200, [], '{"ok":true,"result":{}}')]));
        $result = api::send_test(' -1001234567890 ', 'Hello <team>, *done*');
        $this->assertTrue($result['ok']);
        $history = api::get_history();
        $this->assertCount(1, $history);
        $this->assertSame('-1001234567890', $history[0]['chat_id']);
        $this->assertStringContainsString(s(format_string(get_site()->fullname)), $history[0]['text']);
        $this->assertStringContainsString('Hello &lt;team&gt;, <b>done</b>', $history[0]['text']);

        // A refusal comes back without the token.
        api::set_mock(new MockHandler([new Response(401, [], '{"ok":false,"description":"Unauthorized 123456:SECRET-TOKEN"}')]));
        $result = api::send_test('@team_alerts', 'x');
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('HTTP 401', $result['error']);
        $this->assertStringNotContainsString('SECRET-TOKEN', $result['error']);
    }

    /**
     * An invalid chat id or a missing token sends nothing.
     *
     * @return void
     */
    public function test_send_test_refused(): void {
        api::set_mock(new MockHandler([]));
        $result = api::send_test('not a chat', 'x');
        $this->assertFalse($result['ok']);
        $this->assertSame(get_string('invalidchatid', 'message_telegrambot'), $result['error']);

        set_config('token', '', 'message_telegrambot');
        $result = api::send_test('42', 'x');
        $this->assertFalse($result['ok']);
        $this->assertSame([], api::get_history());
    }

    /**
     * Simple Markdown becomes Telegram HTML, after escaping; snake_case and unsafe links stay as typed.
     *
     * @return void
     */
    public function test_markdown(): void {
        $this->assertSame('<b>bold</b> and <b>bold</b>', api::markdown('*bold* and **bold**'));
        $this->assertSame('<i>italic</i>, <i>also</i> and <s>gone</s>', api::markdown('_italic_, __also__ and ~gone~'));
        $this->assertSame('<code>a *not* b</code> 1 &lt; 2', api::markdown("\x60a *not* b\x60 1 < 2"));
        $this->assertSame('my_var_name and 2*3*4', api::markdown('my_var_name and 2*3*4'));
        $link = api::markdown('[site](https://example.com/?a=1&b=2)');
        $this->assertSame('<a href="https://example.com/?a=1&amp;b=2">site</a>', $link);
        $this->assertSame('[x](javascript:alert(1))', api::markdown('[x](javascript:alert(1))'));
        $this->assertSame('&lt;b&gt;no&lt;/b&gt;', api::markdown('<b>no</b>'));
    }

    /**
     * The HTML of a notification keeps the tags Telegram accepts and drops the rest.
     *
     * @return void
     */
    public function test_html(): void {
        $source = '<p>Hello <strong>team</strong> &amp; <em>all</em></p>' .
            '<ul><li>one</li><li>two</li></ul><p><a href="https://example.com/x?a=1&amp;b=2">open</a> ' .
            '<a href="javascript:alert(1)">bad</a> <span class="x">plain</span><script>alert(1)</script></p>';
        $html = api::html($source);
        $this->assertStringContainsString('Hello <b>team</b> &amp; <i>all</i>', $html);
        $this->assertStringContainsString("• one\n• two", $html);
        $this->assertStringContainsString('<a href="https://example.com/x?a=1&amp;b=2">open</a>', $html);
        $this->assertStringContainsString('bad plain', $html);
        $this->assertStringNotContainsString('javascript', $html);
        $this->assertStringNotContainsString('<span', $html);
        $this->assertStringNotContainsString('alert(1)', $html);
        $this->assertSame('', api::html('  '));
    }

    /**
     * A notification does not repeat its subject nor print its link before the "Open" link.
     *
     * @return void
     */
    public function test_format_drops_repeated_lines(): void {
        $url = 'https://example.com/local/alerthub/index.php';
        $html = api::format('[Info] Hub test', "[Info] Hub test\n\nSent by the channel.\n\n" . $url, $url, 'Open');
        $expected = "<b>[Info] Hub test</b>\n\nSent by the channel.\n\n" . '<a href="' . $url . '">Open</a>';
        $this->assertSame($expected, $html);
    }

    /**
     * A notification with HTML is sent with its formatting.
     *
     * @return void
     */
    public function test_send_message_html(): void {
        $user = $this->getDataGenerator()->create_user();
        set_user_preference(\message_output_telegrambot::PREFERENCE, '42', $user);
        api::set_mock(new MockHandler([new Response(200, [], '{"ok":true,"result":{}}')]));
        $eventdata = $this->eventdata($user);
        $eventdata->fullmessagehtml = '<p>Course <strong>A</strong> &amp; B</p>';

        $this->assertTrue((new \message_output_telegrambot())->send_message($eventdata));
        $this->assertStringContainsString('Course <b>A</b> &amp; B', api::get_history()[0]['text']);
    }
}
