<?php
/**
 * Post-installation logic for local_radiancesis.
 *
 * @package    local_radiancesis
 * @copyright  2026 Horizon Education Network
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Perform post-installation tasks.
 */
function xmldb_local_radiancesis_install()
{
    global $DB, $CFG;

    // 1. Create the Radiance SIS API User.
    $username = 'radiancesis';
    $email = 'api@radiancesis.com';

    if (!$DB->record_exists('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id))) {
        $user = new stdClass();
        $user->username = $username;
        $user->firstname = 'Radiance';
        $user->lastname = 'SIS';
        $user->email = $email;
        $user->auth = 'manual';
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->confirmed = 1;
        $user->timecreated = time();

        // Generate a random complex password.
        // Needs at least 1 upper, 1 lower, 1 digit, 1 non-alphanum.
        $password = 'Rad!' . generate_password(24) . '9#';
        $user->password = hash_internal_user_password($password);

        $userid = $DB->insert_record('user', $user);

        // 2. Grant Site Management Capabilities (Add to site admins).
        $currentadmins = explode(',', $CFG->siteadmins);
        if (!in_array($userid, $currentadmins)) {
            $currentadmins[] = $userid;
            set_config('siteadmins', implode(',', $currentadmins));
        }

        // 3. Register the service and authorize the user.
        // We do this manually here because db/services.php is processed AFTER install.php.
        $service = $DB->get_record('external_services', array('shortname' => 'RadianceSIS', 'component' => 'local_radiancesis'));
        if (!$service) {
            $service = new stdClass();
            $service->name = 'RadianceSIS';
            $service->shortname = 'RadianceSIS';
            $service->component = 'local_radiancesis';
            $service->enabled = 1;
            $service->restrictedusers = 1;
            $service->timecreated = time();
            $service->timemodified = time();
            $service->id = $DB->insert_record('external_services', $service);
        }

        if (!$DB->record_exists('external_services_users', array('externalserviceid' => $service->id, 'userid' => $userid))) {
            $authuser = new stdClass();
            $authuser->externalserviceid = $service->id;
            $authuser->userid = $userid;
            $authuser->timecreated = time();
            $DB->insert_record('external_services_users', $authuser);
        }

        // Note: We don't echo the password here as it's not visible during install.
        // The administrator will need to reset it or we can log it (less secure).
        // Since it's a "Radiance SIS" service user, the admin should generate a token.
    }
}
