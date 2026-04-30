<?php
/**
 * Strings for component 'local_radiancesis', language 'en'
 *
 * @package    local_radiancesis
 * @copyright  2026 Horizon Education Network
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'RadianceSIS';
$string['servicename_radiancesis'] = 'RadianceSIS';
$string['servicename_RadianceSIS'] = 'RadianceSIS';
$string['radiancesis:submitfinalgrades'] = 'Submit final grades to RadianceSIS';

// Settings
$string['settings'] = 'RadianceSIS Settings';
$string['enableplugin'] = 'Enable Plugin';
$string['enableplugin_desc'] = 'If disabled, the entire plugin functionality will be turned off.';
$string['enablewebhook'] = 'Enable Webhook';
$string['enablewebhook_desc'] = 'If enabled, a webhook will be sent to RadianceSIS when final grades are submitted.';
$string['webhookurl'] = 'Webhook URL';
$string['webhookurl_desc'] = 'The endpoint URL for the RadianceSIS webhook. The webhook is only active if this is populated and the webhook is enabled.';

// Grade Submission UI
$string['submitgrades'] = 'Submit Final Grades';
$string['submitgradestosis'] = 'Submit Final Grades to SIS';
$string['nogradesfound'] = 'No grades found for this course.';
$string['gradessubmitted'] = 'Final grades have been successfully submitted to RadianceSIS.';
$string['error_submission'] = 'An error occurred while submitting grades.';
$string['courseidrequired'] = 'Course ID is required.';
$string['error_plugin_disabled'] = 'The RadianceSIS Integration plugin is currently disabled.';
$string['error_missingorgslug'] = 'Unable to save final grades. Please contact your administrator and alert them that the top-level course category "{$a}" must have an idnumber to properly sync with RadianceSIS.';

// Organization Mapping
$string['orgmappingheading'] = 'Organization Mapping';
$string['coursecategories_desc'] = 'To ensure proper integration, please follow these guidelines for course category structure: <br>• <b>Root Level:</b> Primary organization categories must be created at the top (root) level. <br>• <b>Hierarchy:</b> Sub-organizations should be direct children of the top-level organization category. <br>• <b>Shortname:</b> Each organization\'s category must have its <b>Shortname</b> set to the corresponding RadianceSIS organization slug. <br><br>This allows RadianceSIS to correctly place academic terms and courses within the appropriate organization hierarchy.';
$string['orgmappingdesc'] = 'Select the user profile field used to map {$a->users} to their respective organization in RadianceSIS. <br>If you need to create a custom profile field for this purpose, visit the <a href="{$a->url}">{$a->profilefields}</a> page. <br><br><b>Note:</b> It is highly recommended to use a "Dropdown menu" custom profile field to ensure choices are limited and exactly align with the organization SLUG values in RadianceSIS. For the best integration experience, configure the dropdown field as follows: <br>• {$a->required}: Yes <br>• {$a->locked}: Yes <br>• {$a->unique}: No <br>• {$a->signup}: No';
$string['orgfield'] = 'Organization Field';
$string['orgfield_desc'] = 'The field that will be matched against the RadianceSIS organization SLUG.';
$string['customprofilefield'] = 'Custom Profile Field: {$a}';
$string['radiancesisgrades'] = 'RadianceSIS Grades';
$string['savednotstatus'] = 'Saved (Not submitted)';
$string['submittedpendingstatus'] = 'Submitted (Pending retrieval)';
$string['retrievedsyncedstatus'] = 'Retrieved (Synced)';
$string['applyfilters'] = 'Apply Filters';
$string['filternotset'] = 'No final grades have been saved yet.';
$string['filterheader'] = 'Filter Options';
$string['coursename'] = 'Course Name';
$string['usercount'] = 'Users Included';
$string['status'] = 'Status';
$string['timesubmitted'] = 'Time Submitted';
$string['timeretrieved'] = 'Time Retrieved';
$string['savedby'] = 'Saved/Submitted By';

// Events
$string['eventfinalgradessaved'] = 'Final grades saved';
$string['eventfinalgradessubmitted'] = 'Final grades submitted';
$string['eventfinalgradessubmissioncancelled'] = 'Final grades submission cancelled';
$string['eventfinalgradesretrieved'] = 'Final grades retrieved';
