<?php
/**
 * Navigation extensions for local_radiancesis.
 *
 * @package    local_radiancesis
 * @copyright  2026 Horizon Education Network
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extend course navigation with RadianceSIS grade submission link.
 *
 * @param global_navigation $navigation The navigation object to extend.
 * @return void
 */
function local_radiancesis_extend_navigation(global_navigation $navigation) {
    global $COURSE;

    if (empty($COURSE->id) || $COURSE->id <= 1) {
        return;
    }

    $context = context_course::instance($COURSE->id);
    if (!empty($COURSE->idnumber) && has_capability('moodle/grade:viewall', $context) && get_config('local_radiancesis', 'enableplugin')) {
        $url = new moodle_url('/local/radiancesis/submit_grades.php', array('id' => $COURSE->id));
        
        $node = navigation_node::create(
            get_string('submitgrades', 'local_radiancesis'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'radiancesis_submit_grades',
            new pix_icon('i/grades', '', 'moodle')
        );
        
        // Add to the course node. This often puts it in the "More" menu in 4.x.
        $coursenode = $navigation->find($COURSE->id, navigation_node::TYPE_COURSE);
        if ($coursenode) {
            $coursenode->add_node($node);
        }
    }
}

/**
 * Extend the settings navigation (which affects the secondary and tertiary menus).
 *
 * @param settings_navigation $settingsnav The settings navigation object.
 * @param context $context The current context.
 * @return void
 */
function local_radiancesis_extend_settings_navigation(settings_navigation $settingsnav, context $context) {
    global $COURSE;

    if ($context->contextlevel == CONTEXT_COURSE) {
        if (!empty($COURSE->idnumber) && has_capability('moodle/grade:viewall', $context) && get_config('local_radiancesis', 'enableplugin')) {
            
            $url = new moodle_url('/local/radiancesis/submit_grades.php', array('id' => $COURSE->id));
            $node = navigation_node::create(
                get_string('submitgrades', 'local_radiancesis'),
                $url,
                navigation_node::TYPE_SETTING,
                null,
                'radiancesis_submit_grades',
                new pix_icon('i/grades', '', 'moodle')
            );

            // In Moodle 4.x, the 'Grades' tertiary menu pulls from nodes under 'coursegrades'.
            $gradesnode = $settingsnav->find('coursegrades', null);
            if ($gradesnode) {
                $gradesnode->add_node($node);
            }

            // Also add to course administration to ensure it shows up in the "More" menu 
            // if we are not on the grades page.
            $adminnode = $settingsnav->find('courseadmin', null);
            if ($adminnode) {
                $adminnode->add_node($node);
            }
        }
    }
}
