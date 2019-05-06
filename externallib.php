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
 * External functions backported.
 *
 * @package    format_picturelink
 * @copyright
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/course/format/lib.php');

class format_picturelink_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function rewrite_activities_coords_parameters() {
        return new external_function_parameters(
                array(
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'coords' => new external_value(PARAM_TEXT, 'Activities coords', VALUE_OPTIONAL)
                )
        );
    }

    // Rewrite all coords.
    public static function rewrite_activities_coords($courseid, $coords) {

        $params = self::validate_parameters(self::rewrite_activities_coords_parameters(),
                        array(
                            'courseid' => (int) $courseid,
                            'coords' => $coords,
                        )
        );

        $options = array('picturelinkcoords' => $params['coords']);
        $jsondecoded = json_decode($params['coords']);
        $context = context_course::instance($params['courseid']);
        if (has_capability('format/picturelink:editcourseformat', $context)) {
            if (isset($jsondecoded)) {
                return course_get_format($params['courseid'])->update_options_from_ajax($options);
            } else {
                return "no data or error in parsing";
            }
        } else {
            return "No access";
        }
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function rewrite_activities_coords_returns() {
        return new external_value(PARAM_TEXT, 'The result of rewrite activities');
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function rewrite_visible_items_parameters() {
        return new external_function_parameters(
                array(
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'visibleitems' => new external_value(PARAM_TEXT, 'Visible items', VALUE_OPTIONAL)
                )
        );
    }

    // Rewrite visible items.
    public static function rewrite_visible_items($courseid, $visibleitems) {

        $params = self::validate_parameters(self::rewrite_visible_items_parameters(),
                        array(
                            'courseid' => (int) $courseid,
                            'visibleitems' => $visibleitems,
                        )
        );

        $options = array('picturelinkvisibleitems' => $params['visibleitems']);
        $jsondecoded = json_decode($params['visibleitems']);
        $context = context_course::instance($params['courseid']);
        if (has_capability('format/picturelink:editcourseformat', $context)) {
            if (isset($jsondecoded)) {
                return course_get_format($params['courseid'])->update_options_from_ajax($options);
            } else {
                return "no data or error in parsing";
            }
        } else {
            return "No access";
        }
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function rewrite_visible_items_returns() {
        return new external_value(PARAM_TEXT, 'The result of rewrite visible items');
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function rewrite_pinned_sections_parameters() {
        return new external_function_parameters(
                array(
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'pinnedsections' => new external_value(PARAM_TEXT, 'Pinned sections', VALUE_OPTIONAL)
                )
        );
    }

    // Rewrite pinnedsections.
    public static function rewrite_pinned_sections($courseid, $pinnedsections) {

        $params = self::validate_parameters(self::rewrite_pinned_sections_parameters(),
                        array(
                            'courseid' => (int) $courseid,
                            'pinnedsections' => $pinnedsections,
                        )
        );

        $options = array('picturelinkpinnedsections' => $params['pinnedsections']);
        $jsondecoded = json_decode($params['pinnedsections']);
        $context = context_course::instance($params['courseid']);
        if (has_capability('format/picturelink:editcourseformat', $context)) {
            if (isset($jsondecoded)) {
                return course_get_format($params['courseid'])->update_options_from_ajax($options);
            } else {
                return "no data or error in parsing";
            }
        } else {
            return "No access";
        }
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function rewrite_pinned_sections_returns() {
        return new external_value(PARAM_TEXT, 'The result of rewrite pinned sections');
    }

}
