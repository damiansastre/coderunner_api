<?php

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
 * WebServices interface for Certificate API
 *
 * @package    coderunner_api
 * @copyright  2021 Damian Sastre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
        'coderunner_api_get_coderunner_quiz' => array(
                'classname'   => 'coderunner_api_external',
                'methodname'  => 'get_coderunner_quiz',
                'classpath'   => 'local/coderunner_api/externallib.php',
                'description' => 'Returns coderunner questions for quiz with metadata',
                'type'        => 'read',
                'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        ),
        'coderunner_api_check_coderunner_answer' => array(
            'classname'   => 'coderunner_api_external',
            'methodname'  => 'check_coderunner_answer',
            'classpath'   => 'local/coderunner_api/externallib.php',
            'description' => 'Checks a user input answer',
            'type'        => 'write',
            'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        )

);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
#$services = array(
#        'Get CodeRunner quiz questions' => array(
#                'functions' => array ('coderunner_api_get_coderunner_quiz'),
#                'enabled'=>1,
#        )
#);
