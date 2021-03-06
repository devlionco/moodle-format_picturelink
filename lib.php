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
 * This file contains main class for the course format Topic
 *
 * @since     Moodle 2.0
 * @package   format_picturelink
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/lib.php');
require_once($CFG->dirroot . '/course/format/lib.php');

/**
 * Main class for the picturelink course format
 *
 * @package    format_picturelink
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_picturelink extends format_base {

    /**
     * Returns true if this course format uses sections
     *
     * @return bool
     */
    public function uses_sections() {
        return true;
    }

    /**
     * Returns the display name of the given section that the course prefers.
     *
     * Use section name is specified by user. Otherwise use default ("Topic #")
     *
     * @param int|stdClass $section Section object from database or just field section.section
     * @return string Display name that the course format prefers, e.g. "Topic 2"
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if ((string) $section->name !== '') {
            return format_string($section->name, true,
                    array('context' => context_course::instance($this->courseid)));
        } else {
            return $this->get_default_section_name($section);
        }
    }

    /**
     * Returns the default section name for the picturelink course format.
     *
     * If the section number is 0, it will use the string with key = section0name from the course format's lang file.
     * If the section number is not 0, the base implementation of format_base::get_default_section_name which uses
     * the string with the key = 'sectionname' from the course format's lang file + the section number will be used.
     *
     * @param stdClass $section Section object from database or just field course_sections section
     * @return string The default value for the section name.
     */
    public function get_default_section_name($section) {
        if ($section->section == 0) {
            // Return the general section.
            return get_string('section0name', 'format_picturelink');
        } else {
            // Use format_base::get_default_section_name implementation which
            // will display the section name in "Topic n" format.
            return parent::get_default_section_name($section);
        }
    }

    /**
     * The URL to use for the specified course (with section)
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if omitted the course view page is returned
     * @param array $options options for view URL. At the moment core uses:
     *     'navigation' (bool) if true and section has no separate page, the function returns null
     *     'sr' (int) used by multipage formats to specify to which section to return
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = array()) {
        global $CFG;
        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', array('id' => $course->id));

        $sr = null;
        if (array_key_exists('sr', $options)) {
            $sr = $options['sr'];
        }
        if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }
        if ($sectionno !== null) {
            if ($sr !== null) {
                if ($sr) {
                    $usercoursedisplay = COURSE_DISPLAY_MULTIPAGE;
                    $sectionno = $sr;
                } else {
                    $usercoursedisplay = COURSE_DISPLAY_SINGLEPAGE;
                }
            } else {
                $usercoursedisplay = $course->coursedisplay;
            }
            if ($sectionno != 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $url->param('section', $sectionno);
            } else {
                if (empty($CFG->linkcoursesections) && !empty($options['navigation'])) {
                    return null;
                }
                $url->set_anchor('section-' . $sectionno);
            }
        }
        return $url;
    }

    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }

    /**
     * Loads all of the course sections into the navigation
     *
     * @param global_navigation $navigation
     * @param navigation_node $node The course node within the navigation
     */
    public function extend_course_navigation($navigation, navigation_node $node) {
        global $PAGE;
        // If section is specified in course/view.php, make sure it is expanded in navigation.
        if ($navigation->includesectionnum === false) {
            $selectedsection = optional_param('section', null, PARAM_INT);
            if ($selectedsection !== null && (!defined('AJAX_SCRIPT') || AJAX_SCRIPT == '0') &&
                    $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
                $navigation->includesectionnum = $selectedsection;
            }
        }

        // Check if there are callbacks to extend course navigation.
        parent::extend_course_navigation($navigation, $node);

        // We want to remove the general section if it is empty.
        $modinfo = get_fast_modinfo($this->get_course());
        $sections = $modinfo->get_sections();
        if (!isset($sections[0])) {
            // The general section is empty to find the navigation node for it we need to get its ID.
            $section = $modinfo->get_section_info(0);
            $generalsection = $node->get($section->id, navigation_node::TYPE_SECTION);
            if ($generalsection) {
                // We found the node - now remove it.
                $generalsection->remove();
            }
        }
    }

    /**
     * Custom action after section has been moved in AJAX mode
     *
     * Used in course/rest.php
     *
     * @return array This will be passed in ajax respose
     */
    public function ajax_section_move() {
        global $PAGE;
        $titles = array();
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);
        $renderer = $this->get_renderer($PAGE);
        if ($renderer && ($sections = $modinfo->get_section_info_all())) {
            foreach ($sections as $number => $section) {
                $titles[$number] = $renderer->section_title($section, $course);
            }
        }
        return array('sectiontitles' => $titles, 'action' => 'move');
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {
        return array(
            BLOCK_POS_LEFT => array(),
            BLOCK_POS_RIGHT => array()
        );
    }

    /**
     * Definitions of the additional options that this course format uses for course
     *
     * picturelink format uses the following options:
     * - coursedisplay
     * - hiddensections
     * - picturelinkimage
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
        global $DB;
        static $courseformatoptions = false;

        // Help contacts section
        $roles = role_get_names(); // Get all system roles.
        $defaultchoices = [3]; // By defaut - editingteacher role is defined.
        $helprolessection = array();
        $helprolessection['helpcontactroles_title'] = array(
            'label' => get_string('helpcontactroles_label', 'format_picturelink'),
            'element_type' => 'header',
        );
        foreach ($roles as $key => $value) { // Define roles list for help contact.
            if ($key != 16) { // Do not show Supporter role. It is used by default.
                $helprolessection['helpcontactroles_' . $key] = array(
                    'label' => $value->localname,
                    'element_type' => 'advcheckbox',
                    'default' => in_array($value->id, $defaultchoices) ? 1 : 0,
                    'element_attributes' => array(
                        '',
                        array('group' => 1),
                        array(0, 1)
                    ),
                    'help_component' => 'format_picturelink',
                );
            }
        }

        $course = $this->get_course();
        if ($courseformatoptions === false) {
            $courseconfig = get_config('moodlecourse');
            $courseformatoptions = array(
                'hiddensections' => array(
                    'default' => $courseconfig->hiddensections,
                    'type' => PARAM_INT,
                ),
                'coursedisplay' => array(
                    'default' => 1, // SG -- force - 1 - COURSE_DISPLAY_MULTIPAGE - split pages into a page per section.
                    'type' => PARAM_INT,
                ),
                'picturelinkbgcolor' => array(
                    'default' => '#fff',
                    'type' => PARAM_RAW,
                ),
                'picturelinkimage' => array(
                    'default' => '',
                    'type' => PARAM_RAW,
                ),
                'picturelinkcoords' => array(
                    'default' => '',
                    'type' => PARAM_RAW,
                ),
                'picturelinkvisibleitems' => array(
                    'default' => 0,
                    'type' => PARAM_RAW,
                ),
                'picturelinkpinnedsections' => array(
                    'default' => 0,
                    'type' => PARAM_RAW,
                ),
                'displayunits' => array(
                    'label' => get_string('displayunits', 'format_picturelink'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('yes'),
                            0 => new lang_string('no'),
                        )
                    ),
                    'help' => "displayunitsdesc",
                    'help_component' => 'format_picturelink',
                ),
                'displaymessages' => array(
                    'label' => get_string('displaymessages', 'format_picturelink'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('yes'),
                            0 => new lang_string('no'),
                        )
                    ),
                    'help' => "displaymessagesdesc",
                    'help_component' => 'format_picturelink',
                ),
                'displaygrades' => array(
                    'label' => get_string('displaygrades', 'format_picturelink'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('yes'),
                            0 => new lang_string('no'),
                        )
                    ),
                    'help' => "displaygradesdesc",
                    'help_component' => 'format_picturelink',
                ),
                'showbagestag' => array(
                    'label' => get_string('showbagestag', 'format_picturelink'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('yes'),
                            0 => new lang_string('no'),
                        )
                    ),
                    'help' => "showbagestagdesc",
                    'help_component' => 'format_picturelink',
                ),
                'showcertificatestag' => array(
                    'label' => get_string('showcertificatestag', 'format_picturelink'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('yes'),
                            0 => new lang_string('no'),
                        )
                    ),
                    'help' => "showcertificatestagdesc",
                    'help_component' => 'format_picturelink',
                ),
                'showactivityname' => array(
                    'label' => get_string('showactivityname', 'format_picturelink'),
                    'element_type' => 'select',
                    'default' => 0,
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('yes'),
                            0 => new lang_string('no'),
                        )
                    ),
                    'help' => "showactivityname",
                    'help_component' => 'format_picturelink',
                ),
                'showcoursefullname' => array(
                    'label' => get_string('showcoursefullname', 'format_picturelink'),
                    'element_type' => 'advcheckbox',
                    'default' => 1,
                    'element_attributes' => array(
                        '',
                        array('group' => 1),
                        array(0, 1)
                    ),
                    'help' => "showcoursefullnamedesc",
                    'help_component' => 'format_picturelink',
                ),
                'helpcontactroles' => array(
                    'label' => '',
                    'element_type' => 'hidden',
                    'default' => '',
                ),
            );

            // Define display or not "attendanceinfo show/hide setting".
            $attmodid = $DB->get_record('modules', array('name' => 'attendance'), 'id');
            if (!empty($attmodid)){
                $attmodid=$attmodid->id;
            }

            // Get first attedndance instance on current course.
            if ($course) {
                $att = $DB->get_record('course_modules', array(
                    'course' => $course->id,
                    'module' => $attmodid,
                    'deletioninprogress' => 0
                    ),
                    'instance', IGNORE_MULTIPLE);

                if ($att) {
                    $courseformatoptions['displayattendanceinfo'] = array(
                        'label' => get_string('displayattendanceinfo', 'format_picturelink'),
                        'element_type' => 'select',
                        'element_attributes' => array(
                            array(
                                1 => new lang_string('yes'),
                                0 => new lang_string('no'),
                            )
                        ),
                        'help' => "displayattendanceinfodesc",
                        'help_component' => 'format_picturelink',
                    );
                }
            }
            $courseformatoptions = array_merge_recursive($courseformatoptions, $helprolessection);
        }
        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            $courseformatoptionsedit = array(
                'hiddensections' => array(
                    'label' => new lang_string('hiddensections'),
                    'help' => 'hiddensections',
                    'help_component' => 'moodle',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('hiddensectionscollapsed'),
                            1 => new lang_string('hiddensectionsinvisible')
                        )
                    ),
                ),
                'coursedisplay' => array(
                    'label' => new lang_string('coursedisplay'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single'),
                            COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi')
                        )
                    ),
                    'help' => 'coursedisplay',
                    'help_component' => 'moodle',
                ),
                'picturelinkbgcolor' => array(
                    'label' => new lang_string('picturelinkbgcolor', 'format_picturelink'),
                    'element_type' => 'gfcolourpopup',
                    'help' => 'picturelinkbgcolor',
                    'help_component' => 'format_picturelink',
                ),
                'picturelinkimage' => array(
                    'label' => new lang_string('picturelinkimage_form_label', 'format_picturelink'),
                    'element_type' => 'filemanager',
                    'element_attributes' => array(
                    ),
                ),
                'picturelinkcoords' => array(
                    'label' => '',
                    'element_type' => 'hidden',
                ),
                'picturelinkvisibleitems' => array(
                    'label' => '',
                    'element_type' => 'hidden',
                ),
                'picturelinkpinnedsections' => array(
                    'label' => '',
                    'element_type' => 'hidden',
                ),
            );
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }

        return $courseformatoptions;
    }

    /**
     * Function saves ajax data in course format options table from ajax request
     * @param array $options - options to save
     * @return bool
     */
    public function update_options_from_ajax($options) {
        return $this->update_format_options($options);
    }

    /**
     * Adds format options elements to the course/section edit form.
     *
     * This function is called from {@link course_edit_form::definition_after_data()}.
     *
     * @param MoodleQuickForm $mform form the elements are added to.
     * @param bool $forsection 'true' if this is a section edit form, 'false' if this is course edit form.
     * @return array array of references to the added form elements.
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        global $COURSE, $CFG;
        // Import colorpicker form element.
        MoodleQuickForm::registerElementType('gfcolourpopup', "$CFG->dirroot/course/format/picturelink/js/gf_colourpopup.php",
                'moodlequickform_gfcolourpopup');

        $elements = parent::create_edit_form_elements($mform, $forsection);

        if (!$forsection && (empty($COURSE->id) || $COURSE->id == SITEID)) {
            // Add "numsections" element to the create course form - it will force new course to be prepopulated
            // with empty sections.
            // The "Number of sections" option is no longer available when editing course, instead teachers should
            // delete and add sections when needed.
            $courseconfig = get_config('moodlecourse');
            $max = (int) $courseconfig->maxsections;
            $element = $mform->addElement('select', 'numsections', get_string('numberweeks'), range(0, $max ?: 52));
            $mform->setType('numsections', PARAM_INT);
            if (is_null($mform->getElementValue('numsections'))) {
                $mform->setDefault('numsections', $courseconfig->numsections);
            }
            array_unshift($elements, $element);
        }

        // SG - show already uploaded picturelink image in filemanager.
        $context = context_course::instance($COURSE->id);
        $picturelinkimagedraftid = file_get_submitted_draft_itemid('picturelinkimage');
        file_prepare_draft_area($picturelinkimagedraftid, $context->id, 'format_picturelink', 'picturelinkimage', $COURSE->id,
                array('subdirs' => false));
        $mform->setDefault('picturelinkimage', $picturelinkimagedraftid);

        // SG - allow only 1 file upload - ugly hack.
        foreach ($elements as $arr => $element) {
            if (get_class($element) === 'MoodleQuickForm_filemanager') {
                $element->setMaxfiles(1);
                $element->setSubdirs(false);
            }
        }

        return $elements;
    }

    /**
     * Updates format options for a course
     *
     * In case if course format was changed to 'picturelink', we try to copy options
     * 'coursedisplay' and 'hiddensections' from the previous format.
     *
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data
     * @param stdClass $oldcourse if this function is called from {@link update_course()}
     *     this object contains information about the course before update
     * @return bool whether there were any changes to the options values
     */
    public function update_course_format_options($data, $oldcourse = null) {
        // Save picturelink image.
        $course = $this->get_course();
        $context = context_course::instance($course->id);
        $maxbytes = 10000000;
        file_save_draft_area_files($data->picturelinkimage, $context->id, 'format_picturelink', 'picturelinkimage',
                $course->id, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));

        $data = $this->update_helpcontactroles($data);

        $data = (array) $data;
        if ($oldcourse !== null) {
            $oldcourse = (array) $oldcourse;
            $options = $this->course_format_options();
            foreach ($options as $key => $unused) {
                if (!array_key_exists($key, $data)) {
                    if (array_key_exists($key, $oldcourse)) {
                        $data[$key] = $oldcourse[$key];
                    }
                }
            }
        }
        return $this->update_format_options($data);
    }

    /*
     * Update helpcontactroles setting - implode all helpcontactroles settings in a string
     */

    private function update_helpcontactroles($data) {
        $roles = array();
        foreach ($data as $key => $val) {
            if ($val == '1') {
                if (substr($key, 0, 17) === 'helpcontactroles_') {
                    $num = substr($key, strpos($key, "_") + 1);
                    $roles[] = $num;
                }
            }
        }
        $data->helpcontactroles = implode(',', $roles);
        return $data;
    }

    /**
     * Whether this format allows to delete sections
     *
     * Do not call this function directly, instead use {@link course_can_delete_section()}
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function can_delete_section($section) {
        return true;
    }

    /**
     * Prepares the templateable object to display section name
     *
     * @param \section_info|\stdClass $section
     * @param bool $linkifneeded
     * @param bool $editable
     * @param null|lang_string|string $edithint
     * @param null|lang_string|string $editlabel
     * @return \core\output\inplace_editable
     */
    public function inplace_editable_render_section_name($section, $linkifneeded = true,
            $editable = null, $edithint = null, $editlabel = null) {
        if (empty($edithint)) {
            $edithint = new lang_string('editsectionname', 'format_picturelink');
        }
        if (empty($editlabel)) {
            $title = get_section_name($section->course, $section);
            $editlabel = new lang_string('newsectionname', 'format_picturelink', $title);
        }
        return parent::inplace_editable_render_section_name($section, $linkifneeded, $editable, $edithint, $editlabel);
    }

    /**
     * Indicates whether the course format supports the creation of a news forum.
     *
     * @return bool
     */
    public function supports_news() {
        return true;
    }

    /**
     * Returns whether this course format allows the activity to
     * have "triple visibility state" - visible always, hidden on course page but available, hidden.
     *
     * @param stdClass|cm_info $cm course module (may be null if we are displaying a form for adding a module)
     * @param stdClass|section_info $section section where this module is located or will be added to
     * @return bool
     */
    public function allow_stealth_module_visibility($cm, $section) {
        // Allow the third visibility state inside visible sections or in section 0.
        return !$section->section || $section->visible;
    }

    public function section_action($section, $action, $sr) {
        global $PAGE;

        if ($section->section && ($action === 'setmarker' || $action === 'removemarker')) {
            // Format 'picturelink' allows to set and remove markers in addition to common section actions.
            require_capability('moodle/course:setcurrentsection', context_course::instance($this->courseid));
            course_set_marker($this->courseid, ($action === 'setmarker') ? $section->section : 0);
            return null;
        }

        // SG - rewrite format core function section_action, to allow hide/show for sec0.
        $course = $this->get_course();
        $coursecontext = context_course::instance($course->id);
        switch ($action) {
            case 'hide':
            case 'show':
                require_capability('moodle/course:sectionvisibility', $coursecontext);
                $visible = ($action === 'hide') ? 0 : 1;
                course_update_section($course, $section, array('visible' => $visible));
                break;
            default:
                throw new moodle_exception('sectionactionnotsupported', 'core', null, s($action));
        }

        $modules = [];

        $modinfo = get_fast_modinfo($course);
        $coursesections = $modinfo->sections;
        if (array_key_exists($section->section, $coursesections)) {
            $courserenderer = $PAGE->get_renderer('core', 'course');
            $completioninfo = new completion_info($course);
            foreach ($coursesections[$section->section] as $cmid) {
                $cm = $modinfo->get_cm($cmid);
                $modules[] = $courserenderer->course_section_cm_list_item($course, $completioninfo, $cm, $sr);
            }
        }

        $rv = ['modules' => $modules];
        // SG - do not call parent section_action. Rewrite it right here
        // For show/hide actions call the parent method and return the new content for .section_availability element.
        $renderer = $PAGE->get_renderer('format_picturelink');
        $rv['section_availability'] = $renderer->section_availability($this->get_section($section));
        return $rv;
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of configuration settings
     * @since Moodle 3.5
     */
    public function get_config_for_external() {
        // Return everything (nothing to hide).
        return $this->get_format_options();
    }

}

/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 */
function format_picturelink_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
                'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
                array($itemid, 'picturelink'), MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}

/**
 * Serve the files from the MYPLUGIN file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function format_picturelink_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {

    // Make sure the user is logged in and has access to the module
    // (plugins that are not course modules should leave out the 'cm' part).
    require_login($course, true, $cm);

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.
    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.
    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // Variable $args is empty => the path is '/'.
    } else {
        $filepath = '/' . implode('/', $args) . '/'; // Variable $args contains elements of the filepath.
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'format_picturelink', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    // From Moodle 2.3, use send_stored_file instead.
    send_file($file, 86400, 0, $forcedownload, $options);
}
