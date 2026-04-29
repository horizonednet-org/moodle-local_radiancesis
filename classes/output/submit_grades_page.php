<?php
/**
 * Renderable for submit grades page.
 *
 * @package    local_radiancesis
 * @copyright  2026 Horizon Education Network
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_radiancesis\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use moodle_url;

/**
 * Renderable class for submit grades page.
 */
class submit_grades_page implements renderable, templatable {

    /** @var int Course ID */
    protected $courseid;

    /** @var array List of users and grades */
    protected $usersdata;

    protected $status_message;
    protected $status_class;
    protected $can_submit;
    protected $can_save;
    protected $can_cancel;
    protected $is_locked;

    /**
     * Constructor.
     *
     * @param int $courseid
     * @param array $usersdata
     * @param string $status_message
     * @param string $status_class
     * @param bool $can_submit
     * @param bool $can_save
     * @param bool $can_cancel
     * @param bool $is_locked
     */
    public function __construct($courseid, $usersdata, $status_message, $status_class, $can_submit, $can_save, $can_cancel, $is_locked) {
        $this->courseid = $courseid;
        $this->usersdata = $usersdata;
        $this->status_message = $status_message;
        $this->status_class = $status_class;
        $this->can_submit = $can_submit;
        $this->can_save = $can_save;
        $this->can_cancel = $can_cancel;
        $this->is_locked = $is_locked;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        $data = new \stdClass();
        $data->courseid = $this->courseid;
        $data->users = array_values($this->usersdata);
        $data->hasusers = !empty($this->usersdata);
        $data->actionurl = (new moodle_url('/local/radiancesis/submit_grades.php', array('id' => $this->courseid)))->out(false);
        $data->sesskey = sesskey();
        
        $data->status_message = $this->status_message;
        $data->status_class = $this->status_class;
        $data->can_submit = $this->can_submit;
        $data->can_save = $this->can_save;
        $data->can_cancel = $this->can_cancel;
        $data->is_locked = $this->is_locked;

        return $data;
    }
}
