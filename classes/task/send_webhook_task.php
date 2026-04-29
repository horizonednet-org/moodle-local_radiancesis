<?php
/**
 * Task to send a webhook to RadianceSIS.
 *
 * @package    local_radiancesis
 * @copyright  2026 Horizon Education Network
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_radiancesis\task;

defined('MOODLE_INTERNAL') || die();

use core\task\adhoc_task;

/**
 * Task to send a webhook to RadianceSIS.
 */
class send_webhook_task extends adhoc_task {

    /**
     * Execute the task.
     */
    public function execute() {
        global $CFG;

        $customdata = $this->get_custom_data();
        $courseid = $customdata->courseid ?? null;

        if (!$courseid) {
            mtrace('No course ID provided for webhook.');
            return;
        }

        // Check if plugin is enabled.
        if (!get_config('local_radiancesis', 'enableplugin')) {
            mtrace('RadianceSIS plugin is disabled. Webhook aborted.');
            return;
        }

        // Check if webhook is enabled.
        if (!get_config('local_radiancesis', 'enablewebhook')) {
            mtrace('RadianceSIS webhook is disabled. Webhook aborted.');
            return;
        }

        // Get webhook URL.
        $webhookurl = get_config('local_radiancesis', 'webhookurl');
        if (empty($webhookurl)) {
            mtrace('RadianceSIS webhook URL is not configured. Webhook aborted.');
            return;
        }

        mtrace("Sending webhook to RadianceSIS for course ID: {$courseid}");

        require_once($CFG->libdir . '/filelib.php');

        $curl = new \curl();
        // Setup payload. We are just notifying that grades are ready for a course.
        $payload = json_encode(array(
            'event' => 'final_grades_submitted',
            'courseid' => $courseid,
            'timestamp' => time()
        ));

        $options = array(
            'CURLOPT_HTTPHEADER' => array('Content-Type: application/json')
        );

        $response = $curl->post($webhookurl, $payload, $options);
        $info = $curl->get_info();

        if ($curl->get_errno() || empty($info['http_code']) || $info['http_code'] >= 400) {
            mtrace("Webhook failed. HTTP Code: " . ($info['http_code'] ?? 'Unknown') . ". Error: " . $curl->error);
            // Optionally we could throw an exception to retry the task later.
            // throw new \moodle_exception('webhook_failed', 'local_radiancesis');
        } else {
            mtrace("Webhook successful. HTTP Code: " . $info['http_code']);
        }
    }
}
