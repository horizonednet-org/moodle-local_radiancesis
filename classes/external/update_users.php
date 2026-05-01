<?php
/**
 * External API for updating users with organization mapping.
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
 * External API for updating users.
 */
class update_users extends external_api {

    public static function execute_parameters() {
        $core_params = \core_user_external::update_users_parameters();
        
        // Force idnumber to be required in the user structure.
        $user_structure = $core_params->keys['users']->content;
        $user_structure->keys['idnumber']->allownull = false;
        $user_structure->keys['idnumber']->required = VALUE_REQUIRED;
        
        // Make id optional because we can look it up via idnumber.
        $user_structure->keys['id']->required = VALUE_OPTIONAL;

        return new external_function_parameters(array(
            'orgslug' => new external_value(PARAM_RAW, 'RadianceSIS organization slug', VALUE_REQUIRED),
            'users' => $core_params->keys['users']
        ));
    }

    /**
     * Updates users.
     *
     * @param string $orgslug
     * @param array $users
     * @return null
     */
    public static function execute($orgslug, $users) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), array(
            'orgslug' => $orgslug,
            'users' => $users
        ));

        $orgfield = get_config('local_radiancesis', 'orgfield');
        if (empty($orgfield)) {
            $orgfield = 'department';
        }

        $is_core_field = in_array($orgfield, ['institution', 'department']);
        
        $modified_users = $params['users'];

        foreach ($modified_users as &$user) {
            // Resolve Moodle ID via idnumber if not provided.
            if (empty($user['id'])) {
                $moodle_user = $DB->get_record('user', array('idnumber' => $user['idnumber']), 'id');
                if (!$moodle_user) {
                    throw new \invalid_parameter_exception('User with idnumber ' . $user['idnumber'] . ' not found.');
                }
                $user['id'] = $moodle_user->id;
            }

            if ($is_core_field) {
                $user[$orgfield] = $params['orgslug'];
            } else {
                $shortname = str_replace('profile_field_', '', $orgfield);
                
                if (!isset($user['customfields'])) {
                    $user['customfields'] = array();
                }
                
                $found = false;
                foreach ($user['customfields'] as &$cf) {
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

        return \core_user_external::update_users($modified_users);
    }

    /**
     * Returns description of method result value
     *
     * @return null
     */
    public static function execute_returns() {
        return \core_user_external::update_users_returns();
    }
}
