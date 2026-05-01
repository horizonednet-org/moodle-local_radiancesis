<?php
/**
 * Event for retrieving final grades via API.
 *
 * @package    local_radiancesis
 * @copyright  2026 Horizon Education Network
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_radiancesis\event;

defined('MOODLE_INTERNAL') || die();

class final_grades_retrieved extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        return get_string('eventfinalgradesretrieved', 'local_radiancesis');
    }

    public function get_description() {
        return "The user with id '$this->userid' retrieved final grades via API for orgslug '{$this->other['orgslug']}'.";
    }
}
