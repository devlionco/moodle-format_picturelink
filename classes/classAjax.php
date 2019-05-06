<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Picturelink course format.
 *
 * @package format_picturelink
 * @copyright
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/lib.php');

class classAjax {

    private $method;

    public function __construct() {
        $this->method = required_param('method', PARAM_TEXT);
    }

    public function run() {
        // Call ajax metod.
        if (method_exists($this, $this->method)) {
            $method = $this->method;
            return $this->$method();
        } else {
            return 'Wrong method';
        }
    }

    // Rewrite all coords.
    private function rewriteactivitiescoords() {
        global $CFG, $USER, $PAGE, $OUTPUT, $DB;

        $courseid = required_param('courseid', PARAM_INT);
        $activitiescoords = optional_param('coords', '', PARAM_TEXT);
        $options = array('picturelinkcoords' => $activitiescoords);
        $jsondecoded = json_decode($activitiescoords);
        if (isset($jsondecoded)) {
            return course_get_format($courseid)->update_options_from_ajax($options);
        } else {
            return "no data or error in parsing";
        }
    }

    // Rewrite visible items.
    private function rewritevisibleitems() {
        global $CFG, $USER, $PAGE, $OUTPUT, $DB;

        $courseid = required_param('courseid', PARAM_INT);
        $visibleitems = optional_param('visibleitems', '', PARAM_TEXT);
        $options = array('picturelinkvisibleitems' => $visibleitems);
        $jsondecoded = json_decode($visibleitems);
        if (isset($jsondecoded)) {
            return course_get_format($courseid)->update_options_from_ajax($options);
        } else {
            return "no data or error in parsing";
        }
    }

    // Rewrite pinnedsections.
    private function rewritepinnedsections() {
        global $CFG, $USER, $PAGE, $OUTPUT, $DB;

        $courseid = required_param('courseid', PARAM_INT);
        $pinnedsections = optional_param('pinnedsections', '', PARAM_TEXT);
        $options = array('picturelinkpinnedsections' => $pinnedsections);
        $jsondecoded = json_decode($pinnedsections);
        if (isset($jsondecoded)) {
            return course_get_format($courseid)->update_options_from_ajax($options);
        } else {
            return "no data or error in parsing";
        }
    }
}
