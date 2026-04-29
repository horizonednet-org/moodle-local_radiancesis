<?php
/**
 * Settings for local_radiancesis.
 *
 * @package    local_radiancesis
 * @copyright  2026 Horizon Education Network
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_radiancesis', get_string('settings', 'local_radiancesis'));
    $ADMIN->add('localplugins', $settings);
    
    // Add report to Site Administration > Reports
    $ADMIN->add('reports', new admin_externalpage('local_radiancesis_report', get_string('radiancesisgrades', 'local_radiancesis'), new moodle_url('/local/radiancesis/report.php')));

    // Global plugin enable/disable toggle.
    $settings->add(new admin_setting_configcheckbox(
        'local_radiancesis/enableplugin',
        get_string('enableplugin', 'local_radiancesis'),
        get_string('enableplugin_desc', 'local_radiancesis'),
        1
    ));

    // Webhook enable/disable toggle.
    $settings->add(new admin_setting_configcheckbox(
        'local_radiancesis/enablewebhook',
        get_string('enablewebhook', 'local_radiancesis'),
        get_string('enablewebhook_desc', 'local_radiancesis'),
        0
    ));

    // Webhook URL.
    $settings->add(new admin_setting_configtext(
        'local_radiancesis/webhookurl',
        get_string('webhookurl', 'local_radiancesis'),
        get_string('webhookurl_desc', 'local_radiancesis'),
        '',
        PARAM_URL
    ));
    // Organization mapping settings.
    global $CFG;
    $profilefieldurl = new moodle_url('/user/profile/index.php');
    
    // Parameters for localized strings.
    $orgparams = new stdClass();
    $orgparams->users = get_string('users', 'moodle');
    $orgparams->url = $profilefieldurl->out(false);
    $orgparams->profilefields = get_string('profilefields', 'admin');
    $orgparams->required = get_string('required', 'moodle');
    $orgparams->locked = get_string('locked', 'admin');
    $orgparams->unique = get_string('profileforceunique', 'admin');
    $orgparams->signup = get_string('profilesignup', 'admin');

    // Main Section Heading.
    $settings->add(new admin_setting_heading('local_radiancesis_org_heading',
        get_string('orgmappingheading', 'local_radiancesis'),
        ''
    ));

    // Course Categories Sub-section.
    $settings->add(new admin_setting_heading('local_radiancesis_org_categories',
        '--- ' . get_string('coursecategories', 'moodle'),
        get_string('coursecategories_desc', 'local_radiancesis')
    ));

    // Users Sub-section.
    $settings->add(new admin_setting_heading('local_radiancesis_org_users',
        '--- ' . get_string('users', 'moodle'),
        get_string('orgmappingdesc', 'local_radiancesis', $orgparams)
    ));

    $options = [
        'idnumber' => 'idnumber (Core)',
        'institution' => 'institution (Core)',
        'department' => 'department (Core)',
    ];

    global $DB;
    $customfields = $DB->get_records('user_info_field', null, 'sortorder ASC', 'id, shortname, name');
    if ($customfields) {
        foreach ($customfields as $field) {
            $options['profile_field_' . $field->shortname] = get_string('customprofilefield', 'local_radiancesis', $field->name);
        }
    }

    $settings->add(new admin_setting_configselect(
        'local_radiancesis/orgfield',
        get_string('orgfield', 'local_radiancesis'),
        get_string('orgfield_desc', 'local_radiancesis'),
        'idnumber',
        $options
    ));
}
