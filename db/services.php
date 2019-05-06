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
 * Web service external functions and service definitions.
 *
 * @package    format_picturelink
 * @copyright  2019 Devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// We defined the web service functions to install.
$functions = array(
    'format_picturelink_rewritevisibleitems' => array(
        'classname' => 'format_picturelink_external',
        'methodname' => 'rewrite_visible_items',
        'classpath' => 'course/format/picturelink/externallib.php',
        'description' => 'Rewrite visible items',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'format/picturelink:editcourseformat',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'format_picturelink_rewritepinnedsections' => array(
        'classname' => 'format_picturelink_external',
        'methodname' => 'rewrite_pinned_sections',
        'classpath' => 'course/format/picturelink/externallib.php',
        'description' => 'Rewrite pinned sections',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'format/picturelink:editcourseformat',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'format_picturelink_rewriteactivitiescoords' => array(
        'classname' => 'format_picturelink_external',
        'methodname' => 'rewrite_activities_coords',
        'classpath' => 'course/format/picturelink/externallib.php',
        'description' => 'Rewrite activities coords',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'format/picturelink:editcourseformat',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'Picturelink Pins' => array(
        'functions' => array(
            'format_picturelink_rewritevisibleitems',
            'format_picturelink_rewritepinnedsections',
            'format_picturelink_rewriteactivitiescoords'
        ),
        'enabled' => 1,
        'shortname' => 'picturelinc'
    )
);
