<?php
/**
 * Services definition.
 *
 * @package    local_radiancesis
 * @copyright  2026 Horizon Education Network
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
    'local_radiancesis_get_final_grades' => array(
        'classname' => 'local_radiancesis\external\get_final_grades',
        'methodname' => 'execute',
        'classpath' => 'local/radiancesis/classes/external/get_final_grades.php',
        'description' => 'Retrieve final grades submitted to RadianceSIS.',
        'type' => 'read',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'RadianceSIS'),
    ),
    'local_radiancesis_get_users' => array(
        'classname' => 'local_radiancesis\external\get_users',
        'methodname' => 'execute',
        'classpath' => 'local/radiancesis/classes/external/get_users.php',
        'description' => 'Retrieve users mapped to a specific RadianceSIS organization.',
        'type' => 'read',
        'ajax' => true,
        'services' => array('RadianceSIS'),
    ),
    'local_radiancesis_create_users' => array(
        'classname' => 'local_radiancesis\external\create_users',
        'methodname' => 'execute',
        'classpath' => 'local/radiancesis/classes/external/create_users.php',
        'description' => 'Create users and map them to a specific RadianceSIS organization.',
        'type' => 'write',
        'ajax' => true,
        'services' => array('RadianceSIS'),
    ),
    'local_radiancesis_update_users' => array(
        'classname' => 'local_radiancesis\external\update_users',
        'methodname' => 'execute',
        'classpath' => 'local/radiancesis/classes/external/update_users.php',
        'description' => 'Update users and map them to a specific RadianceSIS organization.',
        'type' => 'write',
        'ajax' => true,
        'services' => array('RadianceSIS'),
    ),
);

$services = array(
    'RadianceSIS' => array(
        'shortname' => 'RadianceSIS',
        'name' => 'RadianceSIS',
        'description' => 'Service for RadianceSIS to interact with Moodle.',
        'enabled' => 1,
        'restrictedusers' => 1,
        'functions' => array(
            'core_course_create_categories',
            'core_course_create_courses',
            'core_course_get_categories',
            'core_course_get_courses',
            'core_course_get_courses_by_field',
            'core_course_update_courses',
            'core_enrol_get_enrolled_users',
            'core_enrol_get_users_courses',
            'core_grades_get_gradable_users',
            'core_user_update_users',
            'core_webservice_get_site_info',
            'enrol_manual_enrol_users',
            'enrol_manual_unenrol_users',
            'gradereport_overview_get_course_grades',
            'local_radiancesis_get_final_grades',
            'local_radiancesis_get_users',
            'local_radiancesis_create_users',
            'local_radiancesis_update_users'
        ),
    ),
);
