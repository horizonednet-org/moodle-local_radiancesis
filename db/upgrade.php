<?php
/**
 * Upgrade script for local_radiancesis.
 *
 * @package    local_radiancesis
 * @copyright  2026 Horizon Education Network
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the local_radiancesis plugin.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool Always returns true.
 */
function xmldb_local_radiancesis_upgrade($oldversion) {
    global $DB;

    // No upgrade steps needed for initial alpha release.
    // The current database structure is defined in install.xml.

    return true;
}
