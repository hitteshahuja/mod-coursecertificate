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

/**
 * Plugin event classes are defined here.
 *
 * @package     mod_coursecertificate
 * @copyright   2022 Hittesh Ahuja <hitteshahuja@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_coursecertificate\event;

/**
 * The certificate_expiry_notification_sent event class.
 *
 * @package     mod_coursecertificate
 * @copyright   2022 Hittesh Ahuja <hitteshahuja@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certificate_expiry_notification_sent extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['objecttable'] = 'tool_certificate_issues';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
    /**
     * Create instance of event.
     *
     * @param \stdClass $issue
     * @return certificate_issued
     */
    public static function create_from_notification_sent(\stdClass $coursecertificate, \stdClass $certificate) {
        $data = [
            'objectid' => $coursecertificate->id,
                        'context' => \context_course::instance($coursecertificate->course),
                        'other' => ['expires' => userdate($certificate->expires)]
                ];
        $event = self::create($data);
        $event->add_record_snapshot('coursecertificate', $coursecertificate);
        return $event;
    }
    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventexpirynotificationsent', 'mod_coursecertificate');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' was sent a 
    certificate expiry notification for their certificate that is due to expire on ".$this->other['expires'];
    }
}
