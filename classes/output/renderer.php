<?php
/**
 * Renderer for local_radiancesis.
 *
 * @package    local_radiancesis
 * @copyright  2026 Horizon Education Network
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_radiancesis\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

/**
 * Renderer for local_radiancesis.
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the submit grades page.
     *
     * @param submit_grades_page $page
     * @return string HTML
     */
    protected function render_submit_grades_page(submit_grades_page $page) {
        $data = $page->export_for_template($this);
        return $this->render_from_template('local_radiancesis/submit_grades', $data);
    }
}
