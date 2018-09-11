<?php

require_once(__DIR__.'/../../../config.php');
include_once($CFG->dirroot . '/course/formta/picturelink/classes/classAjax.php');

if (!defined('AJAX_SCRIPT')) {
    define('AJAX_SCRIPT', true);
}

require_login();

$context = context_system::instance();
$PAGE->set_context($context);

require_sesskey();

$ajax = new classAjax();
echo $ajax->run();
