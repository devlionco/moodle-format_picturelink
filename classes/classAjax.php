<?php

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

        $activitiescoords = optional_param('coords', '', PARAM_TEXT);
        $options = array('picturelinkcoords' => $activitiescoords);
        $jsondecoded = json_decode($activitiescoords);
        if (isset($jsondecoded)) {
            // to find out which course to process, we take first cmid from json
            list($course, $cm) = get_course_and_cm_from_cmid($jsondecoded[0]->id);
            return course_get_format($course)->update_options_from_ajax($options);
        } else {
            return "no data or error in parsing";
        }
    }

    //rewrite visible items
    private function rewritevisibleitems() {
        global $CFG, $USER, $PAGE, $OUTPUT, $DB;

        $visibleitems = optional_param('visibleitems', '', PARAM_TEXT);
        $options = array('picturelinkvisibleitems' => $visibleitems);
        $jsondecoded = json_decode($visibleitems);
        if (isset($jsondecoded)) {
            // to find out which course to process, we take first cmid from json
            list($course, $cm) = get_course_and_cm_from_cmid($jsondecoded[0]->id);
            return course_get_format($course)->update_options_from_ajax($options);
        } else {
            return "no data or error in parsing";
        }
    }

    //rewrite pinnedsections
    private function rewritepinnedsections() {
        global $CFG, $USER, $PAGE, $OUTPUT, $DB;

        $pinnedsections = optional_param('pinnedsections', '', PARAM_TEXT);
        $options = array('picturelinkpinnedsections' => $pinnedsections);
        $jsondecoded = json_decode($pinnedsections);
        if (isset($jsondecoded)) {
            // to find out which course to process, we take first cmid from json
            list($course, $cm) = get_course_and_cm_from_cmid($jsondecoded[0]->id);
            return course_get_format($course)->update_options_from_ajax($options);
        } else {
            return "no data or error in parsing";
        }
    }


}
