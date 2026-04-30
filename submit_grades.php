<?php
/**
 * Page to submit final grades to RadianceSIS.
 *
 * @package    local_radiancesis
 * @copyright  2026 Horizon Education Network
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/grade/report/lib.php');

$courseid = required_param('id', PARAM_INT);

// Basic setup.
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course);
require_capability('moodle/grade:viewall', $context);

// Check if plugin is enabled.
if (!get_config('local_radiancesis', 'enableplugin')) {
    throw new moodle_exception('error_plugin_disabled', 'local_radiancesis');
}

$PAGE->set_url('/local/radiancesis/submit_grades.php', array('id' => $courseid));
$PAGE->set_context($context);
$PAGE->set_title(get_string('submitgradestosis', 'local_radiancesis'));
$PAGE->set_heading($course->fullname . ' - Final Grades');

// Handle form submission.
if (data_submitted() && confirm_sesskey()) {
    $action = optional_param('action', '', PARAM_ALPHA);
    global $USER;
    $submittedgrades = $_POST['grades'] ?? array();
    
    $now = time();
    $newstatus = 0;
    if ($action == 'submit') {
        $newstatus = 1;
    } elseif ($action == 'cancel') {
        $newstatus = 0;
    }
    
    // Find top-level category idnumber for orgslug.
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $category = $DB->get_record('course_categories', array('id' => $course->category), '*', MUST_EXIST);
    $pathids = explode('/', trim($category->path, '/'));
    
    // The first element in the path array is the top-level category ID.
    $topcatid = $pathids[0];
    $topcat = $DB->get_record('course_categories', array('id' => $topcatid), '*', MUST_EXIST);
    
    $orgslug = $topcat->idnumber;
    if (empty($orgslug)) {
        throw new \moodle_exception('error_missingorgslug', 'local_radiancesis', '', $topcat->name);
    }

    foreach ($submittedgrades as $userid => $gradedata) {
        $userid = clean_param($userid, PARAM_INT);
        $gradevalue = clean_param($gradedata['grade'] ?? '', PARAM_RAW);
        
        // Fetch the user to get their idnumber.
        $student = $DB->get_record('user', array('id' => $userid), 'id, idnumber', MUST_EXIST);
        
        // Use idnumber if available, otherwise fallback to the internal Moodle ID.
        $studentidnumber = !empty($student->idnumber) ? $student->idnumber : (string)$student->id;

        $record = $DB->get_record('local_radiancesis_final_grades', array('courseid' => $courseid, 'studentidnumber' => $studentidnumber));
        
        if ($record) {
            if ($record->status != 2) {
                if ($action != 'cancel') {
                    $record->grade = $gradevalue;
                }
                $record->timemodified = $now;
                $record->status = $newstatus;
                $record->savedbyid = $USER->id;
                $record->orgslug = $orgslug;
                if ($newstatus == 1) {
                    $record->timesubmitted = $now;
                } else if ($newstatus == 0 && $action == 'cancel') {
                    $record->timesubmitted = null;
                }
                $DB->update_record('local_radiancesis_final_grades', $record);
            }
        } else {
            if ($gradevalue !== '') {
                $newrecord = new stdClass();
                $newrecord->courseid = $courseid;
                $newrecord->studentidnumber = $studentidnumber;
                $newrecord->grade = $gradevalue;
                $newrecord->timecreated = $now;
                $newrecord->timemodified = $now;
                $newrecord->status = $newstatus;
                $newrecord->savedbyid = $USER->id;
                $newrecord->orgslug = $orgslug;
                if ($newstatus == 1) {
                    $newrecord->timesubmitted = $now;
                }
                $DB->insert_record('local_radiancesis_final_grades', $newrecord);
            }
        }
    }
    
    if ($action == 'submit' && get_config('local_radiancesis', 'enablewebhook')) {
        $task = new \local_radiancesis\task\send_webhook_task();
        $task->set_custom_data(array('courseid' => $courseid));
        \core\task\manager::queue_adhoc_task($task);
    }
    
    redirect(new moodle_url('/local/radiancesis/submit_grades.php', array('id' => $courseid)), get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// Fetch enrolled students.
$gradableusers = \grade_report::get_gradable_users($courseid);
$course_item = grade_item::fetch_course_item($courseid);

$savedgrades = $DB->get_records('local_radiancesis_final_grades', array('courseid' => $courseid), '', 'studentidnumber, grade, status, timesubmitted, timeretrieved, timemodified');

// Determine overall status
$has_saved = false;
$has_submitted = false;
$has_synced = false;
$last_saved = 0;
$last_submitted = 0;
$last_retrieved = 0;

if ($savedgrades) {
    foreach ($savedgrades as $sg) {
        if ($sg->status == 0) $has_saved = true;
        if ($sg->status == 1) $has_submitted = true;
        if ($sg->status == 2) $has_synced = true;
        $last_saved = max($last_saved, $sg->timemodified);
        $last_submitted = max($last_submitted, ($sg->timesubmitted ?? 0));
        $last_retrieved = max($last_retrieved, ($sg->timeretrieved ?? 0));
    }
}

$status_message = '';
$status_class = '';
$can_submit = true;
$can_save = true;
$can_cancel = false;
$is_locked = false;

if ($has_synced) {
    $status_message = 'Synced with RadianceSIS';
    if ($last_retrieved) {
        $status_message .= ' (Retrieved: ' . userdate($last_retrieved) . ')';
    }
    $status_class = 'alert-success';
    $can_submit = false;
    $can_save = false;
    $can_cancel = false;
    $is_locked = true;
} else if ($has_submitted) {
    $status_message = 'Pending submission to RadianceSIS';
    if ($last_submitted) {
        $status_message .= ' (Submitted: ' . userdate($last_submitted) . ')';
    }
    $status_class = 'alert-warning';
    $can_submit = false;
    $can_save = false;
    $can_cancel = true;
} else if ($has_saved) {
    $status_message = 'Final grades saved but not submitted for syncing.';
    if ($last_saved) {
        $status_message .= ' (Last saved: ' . userdate($last_saved) . ')';
    }
    $status_class = 'alert-info';
} else {
    $status_message = 'No final grades have been saved yet.';
    $status_class = 'alert-secondary';
}

$usersdata = array();
if ($gradableusers) {
    foreach ($gradableusers as $user) {
        $grade_grade = grade_grade::fetch(array('userid' => $user->id, 'itemid' => $course_item->id));
        
        $calculated_grade = '';
        if ($grade_grade && !is_null($grade_grade->finalgrade)) {
            $calculated_grade = grade_format_gradevalue($grade_grade->finalgrade, $course_item, true, GRADE_DISPLAY_TYPE_REAL, 2);
        }

        $sid = !empty($user->idnumber) ? $user->idnumber : (string)$user->id;
        $currentgrade = isset($savedgrades[$sid]) ? $savedgrades[$sid]->grade : '';
        $usersdata[] = array(
            'id' => $user->id,
            'fullname' => fullname($user),
            'calculatedgrade' => $calculated_grade,
            'currentgrade' => $currentgrade,
            'reporturl' => (new moodle_url('/grade/report/user/index.php', ['id' => $courseid, 'userid' => $user->id]))->out(false)
        );
    }
}

// Prepare renderable.
$renderable = new \local_radiancesis\output\submit_grades_page($courseid, $usersdata, $status_message, $status_class, $can_submit, $can_save, $can_cancel, $is_locked);
$output = $PAGE->get_renderer('local_radiancesis');

echo $OUTPUT->header();
echo $output->render($renderable);
echo $OUTPUT->footer();
