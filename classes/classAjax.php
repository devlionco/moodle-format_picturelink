<?php
include_once($CFG->dirroot . '/filter/teamwork/locallib.php');

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

    //rewrite all cords
    private function rewriteactivitiescoords() {
        global $CFG, $USER, $PAGE, $OUTPUT, $DB;

        $activitiescoords = optional_param('coords', '', PARAM_TEXT);

    }


}
