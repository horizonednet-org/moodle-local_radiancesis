<?php
/**
 * Site-level report of RadianceSIS final grades statuses.
 *
 * @package    local_radiancesis
 * @copyright  2026 Horizon Education Network
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

admin_externalpage_setup('local_radiancesis_report');

$context = context_system::instance();
require_capability('moodle/grade:viewall', $context);

$url = new moodle_url('/local/radiancesis/report.php');
$PAGE->set_url($url);
$PAGE->set_title(get_string('radiancesisgrades', 'local_radiancesis'));
$PAGE->set_heading(get_string('radiancesisgrades', 'local_radiancesis'));

// --- Read filters from GET params ---
$filter_statuses    = optional_param_array('statuses', null, PARAM_INT);
$filter_start_from  = optional_param('start_from', '', PARAM_ALPHANUM);
$filter_start_to    = optional_param('start_to', '', PARAM_ALPHANUM);
$filter_end_from    = optional_param('end_from', '', PARAM_ALPHANUM);
$filter_end_to      = optional_param('end_to', '', PARAM_ALPHANUM);
$filter_sub_from    = optional_param('sub_from', '', PARAM_ALPHANUM);
$filter_sub_to      = optional_param('sub_to', '', PARAM_ALPHANUM);
$filter_ret_from    = optional_param('ret_from', '', PARAM_ALPHANUM);
$filter_ret_to      = optional_param('ret_to', '', PARAM_ALPHANUM);
$filter_org         = optional_param('org', '', PARAM_RAW);

// Helper: convert HTML date input (YYYY-MM-DD) to unix timestamp
function date_to_ts(string $val, bool $end_of_day = false): ?int {
    if (empty($val)) {
        return null;
    }
    $ts = strtotime($val);
    if ($ts === false) {
        return null;
    }
    if ($end_of_day) {
        $ts = strtotime('23:59:59', $ts);
    }
    return $ts;
}

echo $OUTPUT->header();

// ---- Compact filter bar (rendered as plain HTML, no moodleform) ----
// Use string keys throughout to avoid PHP loose-comparison (0 == '') bug.
$selected_statuses = is_array($filter_statuses)
    ? array_map('strval', $filter_statuses)
    : array();

// "All" is active when nothing is selected (= show all) or all three are selected.
$all_active = count($selected_statuses) === 0 || count($selected_statuses) === 3;

$status_btns = array(
    '0' => get_string('savednotstatus', 'local_radiancesis'),
    '1' => get_string('submittedpendingstatus', 'local_radiancesis'),
    '2' => get_string('retrievedsyncedstatus', 'local_radiancesis'),
);

$btn_colors = array('0' => 'info', '1' => 'warning', '2' => 'success');

// Get organizations (top-level categories with idnumber).
$orgs = $DB->get_records_select('course_categories', "parent = 0 AND idnumber IS NOT NULL AND idnumber != ''", [], 'name ASC');
$org_options = array('' => '— All Organizations —');
if ($orgs) {
    foreach ($orgs as $o) {
        $org_options[$o->idnumber] = $o->name;
    }
}

$reseturl = $url->out(false);
?>
<form method="get" action="<?php echo $url->out(false); ?>" id="radiancesis-filter-form" class="mb-3">
    <div class="card">
        <!-- Row 1: Status toggle buttons -->
        <div class="card-body py-2 px-3 border-bottom">
            <div class="d-flex align-items-center flex-wrap" style="gap:0.4rem;">
                <span class="small font-weight-bold mr-2"><?php echo get_string('status', 'local_radiancesis'); ?>:</span>

                <!-- All Statuses button -->
                <button type="button" id="btn-all"
                    class="btn btn-sm <?php echo $all_active ? 'btn-secondary' : 'btn-outline-secondary'; ?>"
                    onclick="toggleAll(this)">
                    All Statuses
                </button>

                <?php foreach ($status_btns as $val => $label):
                    $is_active = $all_active || in_array($val, $selected_statuses, true);
                    $color = $btn_colors[$val];
                ?>
                <!-- Hidden checkbox — drives form submission -->
                <input type="checkbox" name="statuses[]" id="chk_status_<?php echo $val; ?>"
                    value="<?php echo $val; ?>"
                    class="rsis-status-chk d-none"
                    <?php echo $is_active ? 'checked' : ''; ?>>
                <button type="button"
                    id="btn-status-<?php echo $val; ?>"
                    data-chk="chk_status_<?php echo $val; ?>"
                    class="btn btn-sm rsis-status-btn <?php echo $is_active ? "btn-$color" : "btn-outline-$color"; ?>"
                    onclick="toggleStatus(this)">
                    <?php echo htmlspecialchars($label); ?>
                </button>
                <?php endforeach; ?>

                <div class="ml-auto d-flex align-items-center">
                    <span class="small font-weight-bold mr-2">Organization:</span>
                    <select name="org" class="custom-select custom-select-sm" style="min-width: 200px;">
                        <?php foreach ($org_options as $val => $label): ?>
                            <option value="<?php echo htmlspecialchars($val); ?>" <?php echo $filter_org === $val ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Row 2: Date range filters -->
        <div class="card-body py-2 px-3">
            <div class="d-flex flex-wrap align-items-end" style="gap:0.75rem;">

                <div>
                    <label class="small font-weight-bold d-block mb-1">Course Start</label>
                    <div class="d-flex align-items-center">
                        <input type="date" name="start_from" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filter_start_from); ?>" title="Course start from" style="width:130px;">
                        <span class="mx-1 small text-muted">–</span>
                        <input type="date" name="start_to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filter_start_to); ?>" title="Course start to" style="width:130px;">
                    </div>
                </div>

                <div>
                    <label class="small font-weight-bold d-block mb-1">Course End</label>
                    <div class="d-flex align-items-center">
                        <input type="date" name="end_from" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filter_end_from); ?>" title="Course end from" style="width:130px;">
                        <span class="mx-1 small text-muted">–</span>
                        <input type="date" name="end_to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filter_end_to); ?>" title="Course end to" style="width:130px;">
                    </div>
                </div>

                <div>
                    <label class="small font-weight-bold d-block mb-1"><?php echo get_string('timesubmitted', 'local_radiancesis'); ?></label>
                    <div class="d-flex align-items-center">
                        <input type="date" name="sub_from" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filter_sub_from); ?>" title="Submitted from" style="width:130px;">
                        <span class="mx-1 small text-muted">–</span>
                        <input type="date" name="sub_to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filter_sub_to); ?>" title="Submitted to" style="width:130px;">
                    </div>
                </div>

                <div>
                    <label class="small font-weight-bold d-block mb-1"><?php echo get_string('timeretrieved', 'local_radiancesis'); ?></label>
                    <div class="d-flex align-items-center">
                        <input type="date" name="ret_from" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filter_ret_from); ?>" title="Retrieved from" style="width:130px;">
                        <span class="mx-1 small text-muted">–</span>
                        <input type="date" name="ret_to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filter_ret_to); ?>" title="Retrieved to" style="width:130px;">
                    </div>
                </div>

                <div class="d-flex align-items-end" style="padding-bottom:1px;">
                    <button type="submit" class="btn btn-primary btn-sm mr-1"><?php echo get_string('applyfilters', 'local_radiancesis'); ?></button>
                    <a href="<?php echo $reseturl; ?>" class="btn btn-outline-secondary btn-sm"><?php echo get_string('reset', 'moodle'); ?></a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
var statusColors = {'0': 'info', '1': 'warning', '2': 'success'};

function syncAllButton() {
    var btns = document.querySelectorAll('.rsis-status-btn');
    var chks = document.querySelectorAll('.rsis-status-chk');
    var allChecked = Array.from(chks).every(function(c) { return c.checked; });
    var btnAll = document.getElementById('btn-all');
    btnAll.className = 'btn btn-sm ' + (allChecked ? 'btn-secondary' : 'btn-outline-secondary');
}

function toggleStatus(btn) {
    var chkId = btn.getAttribute('data-chk');
    var chk   = document.getElementById(chkId);
    var val   = chk.value;
    var color = statusColors[val] || 'secondary';
    chk.checked = !chk.checked;
    btn.className = 'btn btn-sm rsis-status-btn ' + (chk.checked ? 'btn-' + color : 'btn-outline-' + color);
    syncAllButton();
}

function toggleAll(btn) {
    var chks = document.querySelectorAll('.rsis-status-chk');
    var allChecked = Array.from(chks).every(function(c) { return c.checked; });
    var shouldCheck = !allChecked; // if all were on, turn them off; otherwise turn all on
    chks.forEach(function(chk) {
        chk.checked = shouldCheck;
        var sbtn = document.querySelector('[data-chk="' + chk.id + '"]');
        var color = statusColors[chk.value] || 'secondary';
        sbtn.className = 'btn btn-sm rsis-status-btn ' + (shouldCheck ? 'btn-' + color : 'btn-outline-' + color);
    });
    btn.className = 'btn btn-sm ' + (shouldCheck ? 'btn-secondary' : 'btn-outline-secondary');
}
</script>
<?php

// ---- Build query ----
$where  = array();
$having = array();
$params = array();

$sql = "SELECT c.id, c.fullname, c.shortname,
               COUNT(g.id)            AS usercount,
               MAX(g.status)          AS coursestatus,
               MAX(g.timesubmitted)   AS lastsubmitted,
               MAX(g.timeretrieved)   AS lastretrieved,
               MAX(g.timemodified)    AS lasttimemodified,
               MAX(g.orgslug)         AS orgslug,
               (SELECT u.id
                  FROM {local_radiancesis_final_grades} gsub
                  JOIN {user} u ON u.id = gsub.savedbyid
                 WHERE gsub.courseid = c.id
                   AND gsub.timemodified = (
                       SELECT MAX(gsub2.timemodified)
                         FROM {local_radiancesis_final_grades} gsub2
                        WHERE gsub2.courseid = c.id
                   )
                 LIMIT 1) AS savedbyid,
               (SELECT " . $DB->sql_concat('u2.firstname', "' '" , 'u2.lastname') . "
                  FROM {local_radiancesis_final_grades} gsub3
                  JOIN {user} u2 ON u2.id = gsub3.savedbyid
                 WHERE gsub3.courseid = c.id
                   AND gsub3.timemodified = (
                       SELECT MAX(gsub4.timemodified)
                         FROM {local_radiancesis_final_grades} gsub4
                        WHERE gsub4.courseid = c.id
                   )
                 LIMIT 1) AS savedbyfullname
          FROM {course} c
          JOIN {local_radiancesis_final_grades} g ON g.courseid = c.id
         WHERE 1=1";

$ts_start_from = date_to_ts($filter_start_from);
$ts_start_to   = date_to_ts($filter_start_to, true);
$ts_end_from   = date_to_ts($filter_end_from);
$ts_end_to     = date_to_ts($filter_end_to, true);
$ts_sub_from   = date_to_ts($filter_sub_from);
$ts_sub_to     = date_to_ts($filter_sub_to, true);
$ts_ret_from   = date_to_ts($filter_ret_from);
$ts_ret_to     = date_to_ts($filter_ret_to, true);

if ($ts_start_from) { $where[] = "c.startdate >= :start_from"; $params['start_from'] = $ts_start_from; }
if ($ts_start_to)   { $where[] = "c.startdate <= :start_to";   $params['start_to']   = $ts_start_to;   }
if ($ts_end_from)   { $where[] = "c.enddate >= :end_from";     $params['end_from']   = $ts_end_from;   }
if ($ts_end_to)     { $where[] = "c.enddate <= :end_to";       $params['end_to']     = $ts_end_to;     }
if (!empty($filter_org)) { $where[] = "g.orgslug = :orgslug";         $params['orgslug']    = $filter_org;    }

if (!empty($where)) {
    $sql .= " AND " . implode(" AND ", $where);
}

$sql .= " GROUP BY c.id, c.fullname, c.shortname";

if (is_array($filter_statuses) && count($filter_statuses) > 0) {
    // Use string-normalised values to match the string keys used in the select.
    $clean_statuses = array_map('intval', $filter_statuses);
    list($insql, $inparams) = $DB->get_in_or_equal($clean_statuses, SQL_PARAMS_NAMED, 'status');
    $having[] = "MAX(g.status) $insql";
    $params    = array_merge($params, $inparams);
}
if ($ts_sub_from) { $having[] = "MAX(g.timesubmitted) >= :sub_from"; $params['sub_from'] = $ts_sub_from; }
if ($ts_sub_to)   { $having[] = "MAX(g.timesubmitted) <= :sub_to";   $params['sub_to']   = $ts_sub_to;   }
if ($ts_ret_from) { $having[] = "MAX(g.timeretrieved) >= :ret_from"; $params['ret_from'] = $ts_ret_from; }
if ($ts_ret_to)   { $having[] = "MAX(g.timeretrieved) <= :ret_to";   $params['ret_to']   = $ts_ret_to;   }

if (!empty($having)) {
    $sql .= " HAVING " . implode(" AND ", $having);
}

// ---- Table setup ----
$table = new flexible_table('local-radiancesis-report');
$table->define_baseurl($url);

$columns = array('organization', 'coursename', 'usercount', 'status', 'savedby', 'timesubmitted', 'timeretrieved');
$headers = array(
    get_string('organization', 'local_radiancesis'),
    get_string('coursename', 'local_radiancesis'),
    get_string('usercount', 'local_radiancesis'),
    get_string('status', 'local_radiancesis'),
    get_string('savedby', 'local_radiancesis'),
    get_string('timesubmitted', 'local_radiancesis'),
    get_string('timeretrieved', 'local_radiancesis'),
);

$table->define_columns($columns);
$table->define_headers($headers);
$table->sortable(true, 'coursename', SORT_ASC);
$table->set_attribute('class', 'generaltable w-100');
$table->setup();

$sort = $table->get_sql_sort();
if ($sort) {
    $sort = str_replace('organization',  'orgslug',       $sort);
    $sort = str_replace('coursename',   'c.fullname',    $sort);
    $sort = str_replace('status',       'coursestatus',  $sort);
    $sort = str_replace('timesubmitted','lastsubmitted',  $sort);
    $sort = str_replace('timeretrieved','lastretrieved',  $sort);
    $sort = str_replace('savedby',      'savedbyfullname',$sort);
    $sql .= " ORDER BY $sort";
} else {
    $sql .= " ORDER BY c.fullname ASC";
}

// ---- Render rows ----
$records = $DB->get_records_sql($sql, $params);

if ($records) {
    foreach ($records as $record) {
        $courseurl  = new moodle_url('/local/radiancesis/submit_grades.php', array('id' => $record->id));
        $courselink = html_writer::link($courseurl, $record->fullname);

        if ($record->coursestatus == 2) {
            $badge = '<span class="badge badge-success">'  . get_string('retrievedsyncedstatus', 'local_radiancesis') . '</span>';
        } else if ($record->coursestatus == 1) {
            $badge = '<span class="badge badge-warning">'  . get_string('submittedpendingstatus', 'local_radiancesis') . '</span>';
        } else {
            $badge = '<span class="badge badge-info">'     . get_string('savednotstatus', 'local_radiancesis') . '</span>';
        }

        // Saved-by user link.
        $savedbylink = '—';
        if (!empty($record->savedbyid)) {
            $profileurl = new moodle_url('/user/view.php', array('id' => $record->savedbyid));
            $savedbylink = html_writer::link($profileurl, htmlspecialchars($record->savedbyfullname), array('target' => '_blank'));
        }

        $table->add_data(array(
            $record->orgslug,
            $courselink,
            $record->usercount,
            $badge,
            $savedbylink,
            $record->lastsubmitted ? userdate($record->lastsubmitted) : '—',
            $record->lastretrieved ? userdate($record->lastretrieved) : '—',
        ));
    }
} else {
    echo html_writer::div(get_string('nogradesfound', 'local_radiancesis'), 'alert alert-info mt-3');
}

$table->finish_output();

echo $OUTPUT->footer();
