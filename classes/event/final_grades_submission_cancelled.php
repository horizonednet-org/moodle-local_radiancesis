<?php
/**
 * Event for cancelling final grades submission.
 *
 * @package    local_radiancesis
 * @copyright  2026 Horizon Education Network
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_radiancesis\event;

defined('MOODLE_INTERNAL') || die();

class final_grades_submission_cancelled extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    public static function get_name() {
        return get_string('eventfinalgradessubmissioncancelled', 'local_radiancesis');
    }

    public function get_description() {
        return "The user with id '$this->userid' cancelled the final grades submission for the course with id '$this->courseid'.";
    }

    public function get_url() {
        return new \moodle_url('/local/radiancesis/submit_grades.php', array('id' => $this->courseid));
    }
}
