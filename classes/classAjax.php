<?php
require_once($CFG->dirroot.'/course/format/lib.php');

class classAjax {

    private $method;

    public function __construct() {
        $this->method = required_param('method', PARAM_TEXT);
    }

    public function run()
    {
        //call ajax metod
        if(method_exists($this, $this->method)){
            $method = $this->method;
            return $this->$method();
        }else{
            return 'Wrong method';
        }
    }

    //rewrite all coords
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

    //rewrite visible items
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

    //rewrite pinnedsections
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
