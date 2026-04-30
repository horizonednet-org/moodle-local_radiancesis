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
                'orgslug' => new external_value(PARAM_RAW, 'Organization slug to retrieve grades for', VALUE_REQUIRED),
                'status'  => new external_value(PARAM_INT, 'Status to filter by (0 = saved, 1 = submitted, 2 = retrieved)', VALUE_DEFAULT, 1)
            )
        );
    }

    /**
     * Returns final grades for a course that have been submitted.
     *
     * @param int $orgslug
     * @return array
     */
    public static function execute($orgslug, $status = 1) {
        global $DB;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), array(
            'orgslug' => $orgslug,
            'status'  => $status
        ));

        // System context validation.
        $context = \context_system::instance();
        self::validate_context($context);

        require_capability('moodle/grade:viewall', $context);

        if (!get_config('local_radiancesis', 'enableplugin')) {
            throw new \moodle_exception('error_plugin_disabled', 'local_radiancesis');
        }

        // Fetch grades with the requested status (case-insensitive for orgslug).
        $sql = "SELECT * FROM {local_radiancesis_final_grades} 
                 WHERE " . $DB->sql_compare_text('orgslug') . " = " . $DB->sql_compare_text(':orgslug') . " 
                   AND status = :status";
        $records = $DB->get_records_sql($sql, array(
            'orgslug' => $params['orgslug'],
            'status'  => $params['status']
        ));

        $now = time();
        $grades = array();
        foreach ($records as $record) {
            $grades[] = array(
                'courseid'        => $record->courseid,
                'studentidnumber' => $record->studentidnumber,
                'grade'           => $record->grade,
                'feedback'        => $record->feedback,
                'timecreated'     => $record->timecreated,
                'timemodified'    => $record->timemodified,
                'timesubmitted'   => $record->timesubmitted,
                'status'          => ($params['status'] == 1) ? 2 : $record->status
            );

            // Only transition records if we are pulling 'submitted' grades (status 1).
            if ($params['status'] == 1) {
                $updaterecord = new \stdClass();
                $updaterecord->id = $record->id;
                $updaterecord->status = 2;
                $updaterecord->timeretrieved = $now;
                $updaterecord->timemodified = $now;
                $DB->update_record('local_radiancesis_final_grades', $updaterecord);
            }
        }

        // Trigger event.
        $event = \local_radiancesis\event\final_grades_retrieved::create(array(
            'context' => \context_system::instance(),
            'other' => array('orgslug' => $params['orgslug'])
        ));
        $event->trigger();

        return array(
            'orgslug' => $params['orgslug'],
            'grades'  => $grades
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
                'orgslug' => new external_value(PARAM_RAW, 'Organization Slug'),
                'grades' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'courseid'        => new external_value(PARAM_INT, 'Course ID'),
                            'studentidnumber' => new external_value(PARAM_RAW, 'RadianceSIS student ID (idnumber)'),
                            'grade'           => new external_value(PARAM_RAW, 'Final grade value', VALUE_OPTIONAL),
                            'feedback'        => new external_value(PARAM_RAW, 'Optional feedback', VALUE_OPTIONAL),
                            'timecreated'     => new external_value(PARAM_INT, 'Time created'),
                            'timemodified'    => new external_value(PARAM_INT, 'Time modified'),
                            'timesubmitted'   => new external_value(PARAM_INT, 'Time submitted'),
                            'status'          => new external_value(PARAM_INT, 'Status (0 = saved, 1 = submitted, 2 = retrieved)')
                        )
                    ),
                    'List of grades'
                )
            )
        );
    }
}
