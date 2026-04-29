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
        'classname'   => 'local_radiancesis\external\get_final_grades',
        'methodname'  => 'execute',
        'classpath'   => 'local/radiancesis/classes/external/get_final_grades.php',
        'description' => 'Retrieve final grades submitted to RadianceSIS.',
        'type'        => 'read',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE, 'radiancesis_integration'),
    ),
    'local_radiancesis_get_users' => array(
        'classname'   => 'local_radiancesis\external\get_users',
        'methodname'  => 'execute',
        'classpath'   => 'local/radiancesis/classes/external/get_users.php',
        'description' => 'Retrieve users mapped to a specific RadianceSIS organization.',
        'type'        => 'read',
        'ajax'        => true,
        'services'    => array('radiancesis_integration'),
    ),
    'local_radiancesis_create_users' => array(
        'classname'   => 'local_radiancesis\external\create_users',
        'methodname'  => 'execute',
        'classpath'   => 'local/radiancesis/classes/external/create_users.php',
        'description' => 'Create users and map them to a specific RadianceSIS organization.',
        'type'        => 'write',
        'ajax'        => true,
        'services'    => array('radiancesis_integration'),
    ),
);

$services = array(
    'radiancesis_integration' => array(
        'shortname'   => 'radiancesis_integration',
        'name'        => 'RadianceSIS Integration',
        'description' => 'Service for RadianceSIS to interact with Moodle.',
        'enabled'     => 1,
        'restrictedusers' => 0,
        'functions'   => array(
            'core_course_create_categories',
            'core_course_create_courses',
            'core_course_get_categories',
            'core_course_get_courses',
            'core_course_get_courses_by_field',
            'core_course_update_courses',
            'core_enrol_get_enrolled_users',
            'core_enrol_get_users_courses',
            'core_enrol_unenrol_user_enrolment',
            'core_grades_get_gradable_users',
            'core_user_create_users',
            'core_user_get_users',
            'core_user_get_users_by_field',
            'core_user_search_identity',
            'core_user_update_users',
            'core_webservice_get_site_info',
            'enrol_manual_enrol_users',
            'enrol_manual_unenrol_users',
            'enrol_meta_add_instances',
            'enrol_meta_delete_instances',
            'gradereport_overview_get_course_grades',
            'local_radiancesis_get_final_grades',
            'local_radiancesis_get_users',
            'local_radiancesis_create_users'
        ),
    ),
);
