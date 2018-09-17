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
 * Renderer for outputting the picturelink course format.
 *
 * @package format_picturelink
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');
require_once("{$CFG->libdir}/completionlib.php");

/**
 * Basic renderer for picturelink format.
 *
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_picturelink_renderer extends format_section_renderer_base {


    //TODO
      /**
       * Function gets coordinates for balls, saved in format options table
       * @param $course
       * @return array $coords - rearranged array with cm ids and coordinates
       */
      public function activitiesselection($modinfo) {

        $o = '';
        $o .= html_writer::start_tag('div', array('id'=>'allactivities' , 'class'=>'allactivities-wrapper picturelink_admin'));
          $o .= html_writer::start_tag('div', array('class'=>'allactivities'));
            $o .= html_writer::tag('span', get_string('activities', 'format_picturelink') ,array('class'=>'section-name'));
            $o .= html_writer::tag('span', '' ,array('class'=>'select-icon'));
          $o .= html_writer::end_tag('div');

          $o .= html_writer::start_tag('div', array('id'=>'activities', 'class'=>'section-wrap'));
            $o .= html_writer::start_tag('div', array('class'=>'section-items'));

            foreach ($modinfo->cms as $cm) {
              $visibleclass  = (isset($visibleitems[$cm->id]) ? $visibleitems[$cm->id] : 0) ? ' fa-eye' : ' fa-eye-slash';
              $visibletag = html_writer::tag('i', '' ,array('id'=> 'visibility', 'class'=>'far'.$visibleclass));
              $o .= html_writer::tag('div', $cm->name.$visibletag ,array(
                'class'=>'section-item',
                'data-topid'=>$cm->id
              ));
              // $o .= html_writer::tag('i', '' ,array('class'=>'far fa-eye'));
            }

            $o .= html_writer::end_tag('div');
          $o .= html_writer::end_tag('div');

        $o .= html_writer::end_tag('div');

        return $o;
      }

      //TODO
        /**
         * Function gets coordinates for balls, saved in format options table
         * @param $course
         * @return array $coords - rearranged array with cm ids and coordinates
         */
        public function sectionselection($modinfo ,$cformat) {

          $o = '';
          $o .= html_writer::start_tag('div', array('id'=>'allsections' , 'class'=>'allsection-wrapper picturelink_admin'));
            $o .= html_writer::start_tag('div', array('class'=>'allactivities'));
              $o .= html_writer::tag('span', get_string('sections', 'format_picturelink')  ,array('class'=>'section-name'));
              $o .= html_writer::tag('span', '' ,array('class'=>'select-icon'));
            $o .= html_writer::end_tag('div');

            $o .= html_writer::start_tag('div', array('id'=>'sections', 'class'=>'section-wrap'));
              $o .= html_writer::start_tag('div', array('class'=>'section-items'));

              foreach ($modinfo->sections as $section => $scms) {
                $sinfo = $cformat->get_section($section);
                $sid = "s".$sinfo->id;
                $sname = $cformat->get_section_name($section);

                $visibleclass  = (isset($visibleitems[$sid]) ? $visibleitems[$sid] : 0) ? ' fa-eye' : ' fa-eye-slash';
                $pinnedclass  = (isset($pinnedsections[$sid]) ? $pinnedsections[$sid] : 0) ? ' fa-unlock' : ' fa-lock';
                $visibletag = html_writer::tag('i', '' ,array('id'=> 'visibility', 'class'=>'far'.$visibleclass));
                $pinnedtag = html_writer::tag('i', '' ,array('id'=> 'pinned', 'class'=>'fas'.$pinnedclass));
                $o .= html_writer::tag('div', $sname.$visibletag.$pinnedtag ,array(
                  'class'=>'section-item',
                  'data-topid'=> $sid,
                ));
                // $o .= html_writer::tag('i', '' ,array('class'=>'far fa-eye'));
              }

              $o .= html_writer::end_tag('div');
            $o .= html_writer::end_tag('div');

          $o .= html_writer::end_tag('div');

          return $o;
        }


    /**
     * list of sections and activities on the course
     *
     * @return string HTML to output.
     */
    public function picturelink_get_cms($course, $modinfo) {
        // get sections
        $cformat = course_get_format($course);
        $picturelinkimage = $this->picturelink_get_image($course);
        $coords = $this->picturelink_get_coords($course);
        $visibleitems = $this->picturelink_get_visible_items($course);
        $pinnedsections = $this->picturelink_get_pinnedsections($course);
        $completion = new completion_info($course);

        
        $o = '';
        $o .= html_writer::start_tag('div', array('class' => 'picturelink', 'data-courseid'=>$course->id, 'style' => 'background-image:url('.$picturelinkimage.');'));
        // add button to remove items
        if (is_siteadmin()) {
          $o .= html_writer::start_tag('button', array('id' => 'picturelink_admin', 'class'=>'picturelink_admin'));
          $o .= html_writer::start_tag('div', array('class'=>'picturelink_toggle'));
          $o .= html_writer::tag('div', '' ,array('class'=>'picturelink_pin'));
          $o .= html_writer::end_tag('div');
          $o .= html_writer::tag('div',  get_string('moveitems', 'format_picturelink') ,array('class'=>'picturelink_text'));
          $o .= html_writer::end_tag('button');
        }

        // add select to add properties to all activities
        $o .= $this->activitiesselection($modinfo);

        // add select to add properties to all sections
        $o .= $this->sectionselection($modinfo, $cformat);

        // iterate every cms
        foreach ($modinfo->cms as $cm) {
            $cmcompletiondata = $completion->get_data($cm);

            $activeclass = $cmcompletiondata->completionstate ? ' completed' : '';
            //print_object($cm->getIterator());
            $o .= html_writer::link($cm->url, '', array(
                'class' => 'picturelink_item drag'.$activeclass,
                'data-id' => $cm->id,
                'data-mod_name' => $cm->modname,
                // 'data-name' => $cm->name,
                // 'data-status' => $cmcompletiondata->completionstate,
                'data-tooltip' => 'tooltip',
                'data-placement' => 'top',
                'data-visibility' => isset($visibleitems[$cm->id]) ? $visibleitems[$cm->id] : 0,
                'data-original-title' => $cm->name,
                'data-coordx' => isset($coords[$cm->id]->coordx) ? $coords[$cm->id]->coordx : '',
                'data-coordy' => isset($coords[$cm->id]->coordy) ? $coords[$cm->id]->coordy : '',
            ));
        }

        foreach ($modinfo->sections as $section => $scms) {
            $surl = $cformat->get_view_url($section);
            $sname = $cformat->get_section_name($section);
            $sinfo = $cformat->get_section($section);
            $sid = "s".$sinfo->id;

            $o .= html_writer::link($surl, $sname, array(
                'class' => 'picturelink_item picturelink_section drag',
                'title' => $sname,
                'data-id' => 's'.$sinfo->id,
                'data-mod_name' => 'section',
                // 'data-name' => $cm->name,
                // 'data-status' => $cmcompletiondata->completionstate,
                'data-tooltip' => 'tooltip',
                'data-placement' => 'top',
                'data-visibility' => isset($visibleitems[$sid]) ? $visibleitems[$sid] : 0,
                'data-pinned' => isset($pinnedsections[$sid]) ? $pinnedsections[$sid] : 0,
                'data-original-title' => $sname,
                'data-coordx' => isset($coords[$sid]->coordx) ? $coords[$sid]->coordx : '',
                'data-coordy' => isset($coords[$sid]->coordy) ? $coords[$sid]->coordy : '',
            ));
        }

        $o .= html_writer::end_tag('div');
        return $o;
    }

    /**
     * Function gets image for picturelink background
     * @param $course
     * @return $picturelinkimage - link to background image
     */
    private function picturelink_get_image($course) {
        $context = context_course::instance($course->id);
        $fs = get_file_storage();
        if ($files = $fs->get_area_files($context->id, 'format_picturelink', 'picturelinkimage', $course->id)) {
            foreach ($files as $file) {
                if ($file->get_filename() != '.'){
                    $picturelinkimage = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
                }
            }
        }
        $defaultimageurl = $this->courserenderer->image_url('default-bg', 'format_picturelink');
        $picturelinkimage = isset($picturelinkimage) ? $picturelinkimage : $defaultimageurl;

        return $picturelinkimage;
    }

    /**
     * Function gets coordinates for balls, saved in format options table
     * @param $course
     * @return array $coords - rearranged array with cm ids and coordinates
     */
    private function picturelink_get_coords($course) {
        $rawcoords = json_decode($course->picturelinkcoords);
        $coords = array();
        // rearrange array keys for convenience
        foreach ($rawcoords as $id => $value) {
            $coords[$value->id] = $value;
        }
        return $coords;
    }

    /**
     * Function gets custom visibility (defined from select) for balls, saved in format options table
     * @param $course
     * @return array $visibleitems - array wit ids of vivible items
     */
    private function picturelink_get_visible_items($course) {
        $rawvisibleitems = json_decode($course->picturelinkvisibleitems);
        $visibleitems = array();
        // rearrange array keys for convenience
        foreach ($rawvisibleitems as $id => $value) {
            $visibleitems[$value[0]] = $value[1];
        }
        return $visibleitems;
    }

    /**
     * Function gets custom pinned section (defined from select), saved in format options table
     * @param $course
     * @return array $pinnedsections - array wit ids of pinned sections
     */
    private function picturelink_get_pinnedsections($course) {
        $rawpinnedsections = json_decode($course->picturelinkpinnedsections);
        $pinnedsections = array();
        // rearrange array keys for convenience
        foreach ($rawpinnedsections as $id => $value) {
            $pinnedsections[$value[0]] = $value[1];
        }
        return $pinnedsections;
    }

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        // Since format_picturelink_renderer::section_edit_controls() only displays the 'Set current section' control when editing mode is on
        // we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'topics'));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('topicoutline');
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Generate the edit control items of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of edit control items
     */
    protected function section_edit_control_items($course, $section, $onsectionpage = false) {
        global $PAGE;

        if (!$PAGE->user_is_editing()) {
            return array();
        }

        $coursecontext = context_course::instance($course->id);

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $controls = array();
        if ($section->section && has_capability('moodle/course:setcurrentsection', $coursecontext)) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $markedthistopic = get_string('markedthistopic');
                $highlightoff = get_string('highlightoff');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marked',
                                               'name' => $highlightoff,
                                               'pixattr' => array('class' => '', 'alt' => $markedthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markedthistopic,
                                                   'data-action' => 'removemarker'));
            } else {
                $url->param('marker', $section->section);
                $markthistopic = get_string('markthistopic');
                $highlight = get_string('highlight');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marker',
                                               'name' => $highlight,
                                               'pixattr' => array('class' => '', 'alt' => $markthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markthistopic,
                                                   'data-action' => 'setmarker'));
            }
        }

        $parentcontrols = parent::section_edit_control_items($course, $section, $onsectionpage);

        // If the edit key exists, we are going to insert our controls after it.
        if (array_key_exists("edit", $parentcontrols)) {
            $merged = array();
            // We can't use splice because we are using associative arrays.
            // Step through the array and merge the arrays.
            foreach ($parentcontrols as $key => $action) {
                $merged[$key] = $action;
                if ($key == "edit") {
                    // If we have come to the edit key, merge these controls here.
                    $merged = array_merge($merged, $controls);
                }
            }

            return $merged;
        } else {
            return array_merge($controls, $parentcontrols);
        }
    }

    /**
     * Output the html for a multiple section page
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $PAGE;

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();

        $context = context_course::instance($course->id);

        // Render here the picturelink image with cms above all course format
        echo $this->picturelink_get_cms($course, $modinfo);

        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, 0);

        // Now the list of sections..
        echo $this->start_section_list();
        $numsections = course_get_format($course)->get_last_section_number();

        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if ($section == 0) {
                // 0-section is displayed a little different then the others
                if ($thissection->summary or !empty($modinfo->sections[0]) or $PAGE->user_is_editing()) {
                    echo $this->section_header($thissection, $course, false, 0);
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                    echo $this->courserenderer->course_section_add_cm_control($course, 0, 0);
                    echo $this->section_footer();
                }
                continue;
            }
            if ($section > $numsections) {
                // activities inside this section are 'orphaned', this section will be printed as 'stealth' below
                continue;
            }
            // Show the section if the user is permitted to access it, OR if it's not available
            // but there is some available info text which explains the reason & should display.
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available &&
                    !empty($thissection->availableinfo));
            if (!$showsection) {
                // If the hiddensections option is set to 'show hidden sections in collapsed
                // form', then display the hidden section message - UNLESS the section is
                // hidden by the availability system, which is set to hide the reason.
                if (!$course->hiddensections && $thissection->available) {
                    echo $this->section_hidden($section, $course->id);
                }

                continue;
            }

            if (!$PAGE->user_is_editing() && $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                // Display section summary only.
                echo $this->section_summary($thissection, $course, null);
            } else {
                echo $this->section_header($thissection, $course, false, 0);
                if ($thissection->uservisible) {
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                    echo $this->courserenderer->course_section_add_cm_control($course, $section, 0);
                }
                echo $this->section_footer();
            }
        }

        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
            // Print stealth sections if present.
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $numsections or empty($modinfo->sections[$section])) {
                    // this is not stealth section or it is empty
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }

            echo $this->end_section_list();

            echo $this->change_number_sections($course, 0);
        } else {
            echo $this->end_section_list();
        }

    }
}
