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
 * Grid Information
 *
 * @package    course/format
 * @subpackage Grid
 * @version    See the value of '$plugin->version' in version.php.
 * @copyright  &copy; 2012 G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - {@link http://about.me/gjbarnard} and
 *                           {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Provides the information to backup grid course format
 */
class backup_format_picturelink_plugin extends backup_format_plugin {

    /**
     * Returns the format information to attach to course element
     */
    protected function define_course_plugin_structure() {

        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '/course/format', 'picturelink');

        // Create one standard named plugin element (the visible container).
        // The courseid not required as populated on restore.
        $pluginwrapper = new backup_nested_element('picturelinkimages');

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        // Add picture to course.xml.
        $picturelink = new backup_nested_element('picturelinkimage', null, array(
            'picturelinkimagehash',
            'picturelinkimagepath',
            'picturelinkimagename',
            'picturelinkimageauthor',
            'picturelinkimagelicense'));
        $pluginwrapper->add_child($picturelink);

        $images = $this->get_picturelink_images();
        $picturelink->set_source_array(array((object) $images));

        // Don't need to annotate ids nor files.
        return $plugin;
    }

    protected function get_picturelink_images() {
        global $DB;

        $courseid = $this->task->get_courseid();
        $context = context_course::instance($courseid);
        $contextid = $context->id;

        $imagerecord = $DB->get_record_sql('
            SELECT *
            FROM {files}
            WHERE itemid = :courseid
                AND component = "format_picturelink"
                AND filearea = "picturelinkimage"
                AND contextid = :contextid
                AND filesize > 0
            ORDER BY timemodified
            DESC LIMIT 1',
            ['courseid' => $courseid, 'contextid' => $contextid]);

        // Build fullpath of the image.
        $imagepath = $this->get_fulldir_from_hash($imagerecord->contenthash) . '/' . $imagerecord->contenthash;

        $imagedata = file_get_contents($imagepath);

        $base64imagedata = base64_encode($imagedata);

        return array(
            'picturelinkimagehash' => $base64imagedata,
            'picturelinkimagepath' => $imagerecord->filepath,
            'picturelinkimagename' => $imagerecord->filename,
            'picturelinkimageauthor' => $imagerecord->author,
            'picturelinkimagelicense' => $imagerecord->license
        );
    }

    /**
     * Returns the format information to attach to section element
     */
    protected function define_section_plugin_structure() {

        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, $this->get_format_condition(), 'picturelink');

        // Don't need to annotate ids nor files.
        return $plugin;
    }

    /**
     * Get the full directory to the stored file, including the path to the
     * filedir, and the directory which the file is actually in.
     *
     * Note: This function does not ensure that the file is present on disk.
     *
     * @param stored_file $file The file to fetch details for.
     * @return string The full path to the content directory
     */
    protected function get_fulldir_from_hash($contenthash) {
        global $CFG;
        return $CFG->dataroot . '/filedir/' . $this->get_contentdir_from_hash($contenthash);
    }

    /**
     * Get the content directory for the specified content hash.
     * This is the directory that the file will be in, but without the
     * fulldir.
     *
     * @param string $contenthash The content hash
     * @return string The directory within filedir
     */
    protected function get_contentdir_from_hash($contenthash) {
        $l1 = $contenthash[0] . $contenthash[1];
        $l2 = $contenthash[2] . $contenthash[3];
        return "$l1/$l2";
    }

}
