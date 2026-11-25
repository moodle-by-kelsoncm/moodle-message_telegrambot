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
}
