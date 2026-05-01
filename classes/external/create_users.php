<?php
/**
 * External API for creating users with organization mapping.
 *
 * @package    local_radiancesis
 * @copyright  2026 Horizon Education Network
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_radiancesis\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/user/externallib.php');

use external_api;
use external_function_parameters;
use external_value;

/**
 * External API for creating users.
 */
class create_users extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        $core_params = \core_user_external::create_users_parameters();
        
        // Force idnumber to be required in the user structure.
        $user_structure = $core_params->keys['users']->content;
        $user_structure->keys['idnumber']->allownull = false;
        $user_structure->keys['idnumber']->required = VALUE_REQUIRED;

        return new external_function_parameters(array(
            'orgslug' => new external_value(PARAM_RAW, 'RadianceSIS organization slug', VALUE_REQUIRED),
            'users' => $core_params->keys['users']
        ));
    }

    /**
     * Creates users.
     *
     * @param string $orgslug
     * @param array $users
     * @return array
     */
    public static function execute($orgslug, $users) {
        $params = self::validate_parameters(self::execute_parameters(), array(
            'orgslug' => $orgslug,
            'users' => $users
        ));

        // core_user_external::create_users handles capability and context checks
        // via require_capability('moodle/user:create', ...).

        $orgfield = get_config('local_radiancesis', 'orgfield');
        if (empty($orgfield)) {
            $orgfield = 'department'; // fallback
        }

        $is_core_field = in_array($orgfield, ['institution', 'department']);
        
        $modified_users = $params['users'];

        foreach ($modified_users as &$user) {
            if ($is_core_field) {
                // Set the core field directly.
                $user[$orgfield] = $params['orgslug'];
            } else {
                // Set the custom profile field.
                $shortname = str_replace('profile_field_', '', $orgfield);
                
                if (!isset($user['customfields'])) {
                    $user['customfields'] = array();
                }
                
                // Check if the field is already provided; if so, overwrite it.
                $found = false;
                foreach ($user['customfields'] as &$cf) {
                    // Moodle core API expects 'type' as the shortname for custom profile fields.
                    if ($cf['type'] === $shortname) {
                        $cf['value'] = $params['orgslug'];
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $user['customfields'][] = array(
                        'type' => $shortname,
                        'value' => $params['orgslug']
                    );
                }
            }
        }

        return \core_user_external::create_users($modified_users);
    }

    /**
     * Returns description of method result value
     *
     * @return \external_multiple_structure
     */
    public static function execute_returns() {
        return \core_user_external::create_users_returns();
    }
}
