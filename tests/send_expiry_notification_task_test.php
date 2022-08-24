<?php
// This file is part of the mod_coursecertificate plugin for Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_coursecertificate;

use advanced_testcase;
use tool_certificate\certificate;
use tool_certificate_generator;

/**
 * Unit test for the task.
 *
 * @package     mod_coursecertificate
 * @category    test
 * @copyright   2022 Hittesh Ahuja <hittesh@thecrewacademy.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_expiry_notification_task_test extends advanced_testcase {
    /**
     * Set up
     */
    public function setUp(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Get certificate generator
     *
     * @return tool_certificate_generator
     */
    protected function get_certificate_generator() : tool_certificate_generator {
        return $this->getDataGenerator()->get_plugin_generator('tool_certificate');
    }

    /**
     * Test send_expiry_notification_task.
     */
    public function test_send_expiry_notification_task_notification_empty() {
        // Test the generated message.
        global $DB;
        // Create course, certificate template and coursecertificate module.
        $course = $this->getDataGenerator()->create_course(['shortname' => 'C01']);
        $certificate1 = $this->get_certificate_generator()->create_template((object)['name' => 'Certificate 1']);
        $expirydate = strtotime('now');
        $record = ['course' => $course->id, 'template' => $certificate1->get_id(), 'expirydatetype' =>
        certificate::DATE_EXPIRATION_ABSOLUTE,
                'expirydateoffset' => $expirydate, 'expirynotificationdateoffset' => 1, 'expirynotificationmessage' => ''];
        $mod = $this->getDataGenerator()->create_module('coursecertificate', $record);
        // Create user with 'student' role.
        $user1 = $this->getDataGenerator()->create_and_enrol($course);
        // Sanity check.
        $this->assertTrue($DB->record_exists('coursecertificate', ['course' => $course->id, 'id' => $mod->id]));
        // Issue the certificate.
        $this->assertNotEmpty(helper::issue_certificate($user1, $mod, $course));
        $issue = $DB->get_record('tool_certificate_issues', ['templateid' => $certificate1->get_id(),
        'courseid' => $course->id]);
        $this->assertEquals($user1->id, $issue->userid);
        // Check that notif has not been sent.
        $this->assertEquals(0, $issue->expirynotifsent);
        // Run task.
        $task = new \mod_coursecertificate\task\send_expiry_notification_task();
        ob_start();
        $task->execute();
        ob_end_clean();
        // Check that the notification flag has been updated.
        $issue = $DB->get_record('tool_certificate_issues', ['templateid' => $certificate1->get_id(),
        'courseid' => $course->id]);
        $this->assertEquals(1, $issue->expirynotifsent);
        // Get all the users who need the expiry notificatio sent.
        $coursecertificate = new \stdClass();
        $coursecertificate->course = $course->id;
        $coursecertificate->template = $certificate1->get_id();
        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->get_cm($mod->cmid);
        $users = helper::get_users_to_send_expiry_notifications($coursecertificate, $cm);
        $this->assertEmpty($users);
    }
}
