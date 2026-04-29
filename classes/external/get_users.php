<?php
/**
 * External API for getting users filtered by organization.
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
use external_multiple_structure;
use external_single_structure;

/**
 * External API for retrieving users.
 */
class get_users extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(array(
            'orgslug' => new external_value(PARAM_RAW, 'RadianceSIS organization slug', VALUE_REQUIRED),
            'criteria' => new external_multiple_structure(
                new external_single_structure(array(
                    'key' => new external_value(PARAM_ALPHA, 'the user column to search, expected keys (value format) are:
                                "id" (int) matching user id,
                                "lastname" (string) user last name,
                                "firstname" (string) user first name,
                                "idnumber" (string) matching user idnumber,
                                "username" (string) matching user username,
                                "email" (string) user email,
                                "auth" (string) matching user auth plugin'),
                    'value' => new external_value(PARAM_RAW, 'the value to search')
                )), 'the key/value pairs to be considered in user search.', VALUE_DEFAULT, array()
            )
        ));
    }

    /**
     * Returns users.
     *
     * @param string $orgslug
     * @param array $criteria
     * @return array
     */
    public static function execute($orgslug, $criteria = array()) {
        $params = self::validate_parameters(self::execute_parameters(), array(
            'orgslug' => $orgslug,
            'criteria' => $criteria
        ));

        // core_user_external::get_users handles capability and context checks
        // via user_get_user_details_courses.

        $orgfield = get_config('local_radiancesis', 'orgfield');
        if (empty($orgfield)) {
            $orgfield = 'idnumber'; // fallback
        }

        $core_criteria = $params['criteria'];

        $is_core_field = in_array($orgfield, ['idnumber', 'institution', 'department']);
        
        // core_user_external::get_users only supports a limited set of criteria fields.
        // We can only offload 'idnumber' filtering to the DB.
        if ($is_core_field && $orgfield === 'idnumber') {
            $core_criteria[] = array(
                'key' => 'idnumber',
                'value' => $params['orgslug']
            );
            $is_core_field_supported = true;
        } else {
            $is_core_field_supported = false;
        }

        // Delegate to core Moodle function.
        $result = \core_user_external::get_users($core_criteria);

        // If the configured field is a custom profile field or a core field not supported
        // in $criteria (like institution), filter in memory.
        if (!$is_core_field_supported) {
            $filtered_users = array();
            foreach ($result['users'] as $user) {
                $match = false;
                
                if ($is_core_field) {
                    if (isset($user[$orgfield]) && $user[$orgfield] === $params['orgslug']) {
                        $match = true;
                    }
                } else {
                    $shortname = str_replace('profile_field_', '', $orgfield);
                    if (!empty($user['customfields'])) {
                        foreach ($user['customfields'] as $cf) {
                            // Moodle core API returns 'shortname' in the customfields array.
                            if ($cf['shortname'] === $shortname && $cf['value'] === $params['orgslug']) {
                                $match = true;
                                break;
                            }
                        }
                    }
                }
                
                if ($match) {
                    $filtered_users[] = $user;
                }
            }
            $result['users'] = $filtered_users;
        }

        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return \core_user_external::get_users_returns();
    }
}
