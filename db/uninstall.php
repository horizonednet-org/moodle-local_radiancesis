<?php
/**
 * Post-uninstallation logic for local_radiancesis.
 *
 * @package    local_radiancesis
 * @copyright  2026 Horizon Education Network
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Perform post-uninstallation tasks.
 */
function xmldb_local_radiancesis_uninstall() {
    global $DB, $CFG;

    // 1. Find the Radiance SIS API User.
    $username = 'radiancesis';
    $user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id));
    
    if ($user) {
        // 2. Remove from Site Administrators list.
        $currentadmins = explode(',', $CFG->siteadmins);
        $key = array_search($user->id, $currentadmins);
        if ($key !== false) {
            unset($currentadmins[$key]);
            set_config('siteadmins', implode(',', $currentadmins));
        }

        // 3. Delete the user record.
        // We use delete_user() if available, otherwise just mark as deleted.
        require_once($CFG->dirroot . '/user/lib.php');
        delete_user($user);
    }

    return true;
}
