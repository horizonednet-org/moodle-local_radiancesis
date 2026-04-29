<?php
/**
 * External API for retrieving final grades.
 *
 * @package    local_radiancesis
 * @copyright  2026 Horizon Education Network
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_radiancesis\external;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use context_course;

/**
 * External API for retrieving final grades.
 */
class get_final_grades extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'ID of the course', VALUE_REQUIRED)
            )
        );
    }

    /**
     * Returns final grades for a course that have been submitted.
     *
     * @param int $courseid
     * @return array
     */
    public static function execute($courseid) {
        global $DB;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), array(
            'courseid' => $courseid
        ));

        // Context validation.
        $context = context_course::instance($params['courseid']);
        self::validate_context($context);

        // Require capability to view all grades in the course.
        require_capability('moodle/grade:viewall', $context);

        // Check if plugin is enabled.
        if (!get_config('local_radiancesis', 'enableplugin')) {
            throw new \moodle_exception('error_plugin_disabled', 'local_radiancesis');
        }

        // Fetch grades.
        $records = $DB->get_records('local_radiancesis_final_grades', array('courseid' => $params['courseid']));

        $grades = array();
        foreach ($records as $record) {
            $grades[] = array(
                'userid'       => $record->userid,
                'grade'        => $record->grade,
                'feedback'     => $record->feedback,
                'timecreated'  => $record->timecreated,
                'timemodified' => $record->timemodified,
                'status'       => $record->status
            );
        }

        return array(
            'courseid' => $params['courseid'],
            'grades'   => $grades
        );
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course ID'),
                'grades' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'userid'       => new external_value(PARAM_INT, 'User ID'),
                            'grade'        => new external_value(PARAM_RAW, 'Final grade value', VALUE_OPTIONAL),
                            'feedback'     => new external_value(PARAM_RAW, 'Optional feedback', VALUE_OPTIONAL),
                            'timecreated'  => new external_value(PARAM_INT, 'Time created'),
                            'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                            'status'       => new external_value(PARAM_INT, 'Status (0 = pending, 1 = retrieved)')
                        )
                    ),
                    'List of grades'
                )
            )
        );
    }
}
