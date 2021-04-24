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
 * @package    local_certificateapi
 * @copyright  2017 Ian Wild
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . "/externallib.php");
require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot.'/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/course/format/lib.php');

define('DEBUG_QUERIES', true);
define('DEBUG_TRACE', true);

/**
 * Declaration of the Certificate API class. Contains two fuctions:
 * 
 * 1) get_certificates_by_email()
 * 2) get_certificates_by_username()
 * 
 * See method declarations for more details.
 * 
 * @author Ian Wild
 *
 */
class coderunner_api_external extends external_api {

    public static function get_test_cases($testcases)
    {
        $data = [];
        foreach ($testcases as $testcase){
            $data[] = array('testcode' => $testcase->testcode, 'expected' => $testcase->expected);
        }
        return $data;
    }

	public static function get_coderunner_quiz_parameters() {
        return new external_function_parameters(
            array('quizid' => new external_value(PARAM_TEXT, 'The ID of the question'),
                  'attemptid' => new external_value(PARAM_TEXT, 'hehe'))
        );
    }

    public static function get_coderunner_quiz($quizid = '', $attemptid='') {

       # $params = self::validate_parameters(self::hello_world_parameters(),
      #          array('quizid' => $questionid));
        global $DB, $USER;
        $quiz = $DB->get_record('quiz', array('id' => $quizid));
        $course = $DB->get_record('course', array('id' => $quiz->course));
        $cm = get_coursemodule_from_instance("quiz", $quiz->id, $course->id);
        $attemptobj = quiz_attempt::create($attemptid);
        $data = [];
        foreach ($attemptobj->get_slots() as $slot) {
            $qtype = $attemptobj->get_question_type_name($slot);
            $qattempt = $attemptobj->get_question_attempt($slot);
            if ($qtype == 'coderunner'){
                $question_data = $qattempt->get_question();
            #    $response = array('answer' => "function sq = sqr(n)\n  sq = n * n;\nend\n");
                #error_log(print_r($question_data->grade_response($response, FALSE, 0), TRUE));
                $data[$slot] = array("test_cases" => self::get_test_cases($question_data-> testcases),
                                     "template" => base64_encode($question_data->template),
                                     "name" => $question_data->name,
                                     "question_text" => base64_encode($question_data->questiontext),
                                     "type" => $question_data->coderunnertype,
                    );
            }
        }
        return array("questions" =>  $data);
    }

    public static function get_coderunner_quiz_returns() {
        return new external_single_structure(
                array(
                        'questions' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'test_cases' => new external_multiple_structure(
                                        new external_single_structure(
                                            array("testcode" => new external_value(PARAM_TEXT, 'Code to Execute'),
                                                  "expected" => new external_value(PARAM_TEXT, 'Expected Code'))
                                        )
                                    ),
                                    'template' => new external_value(PARAM_TEXT, 'Question Test Template'),
                                    'name' => new external_value(PARAM_TEXT, 'Name of Question'),
                                    'question_text' => new external_value(PARAM_TEXT, 'Question Text'),
                                    'type' => new external_value(PARAM_TEXT, 'question runner type'),
                                )
                            ), 'Quiz questions coderunner handles', VALUE_OPTIONAL
                        )
                     )
        );
    }


    public static function check_coderunner_answer_parameters() {
        return new external_function_parameters(
            array('quizid' => new external_value(PARAM_TEXT, 'The ID of the question'),
                  'attemptid' => new external_value(PARAM_TEXT, 'The ID of the attempt'),
                  'slot' => new external_value(PARAM_TEXT, 'Slot Number'),
                  'page' => new external_value(PARAM_TEXT, 'Page Number'),
                  'answer' => new external_value(PARAM_TEXT, "answer base64 encoded"))
        );
    }

    public static function check_coderunner_answer($quizid = '', $attemptid='', $slot='', $page='', $answer='') {
        global $DB, $USER;
        $quiz = $DB->get_record('quiz', array('id' => $quizid));
        $course = $DB->get_record('course', array('id' => $quiz->course));
        $cm = get_coursemodule_from_instance("quiz", $quiz->id, $course->id);
        $attemptobj = quiz_attempt::create($attemptid);
        $data = array();
        foreach ($attemptobj->get_slots($page) as $quiz_slot) {
            if ($quiz_slot == $slot){
                $qtype = $attemptobj->get_question_type_name($slot);
                $qattempt = $attemptobj->get_question_attempt($slot);
                if ($qtype == 'coderunner'){
                    $question_data = $qattempt->get_question();
                     $response = array('answer' => base64_decode($answer));
                    $grade = $question_data->grade_response($response, FALSE, 0);
                    $outcome = unserialize($grade[2]['_testoutcome']);
                    foreach($outcome->testresults as $testresult){
                        $response = array("testcode" => $testresult->testcode,
                                          "expected" => $testresult->expected,
                                          "got" => $testresult->got,
                                          "mark" => $testresult->mark,
                                          "iscorrect" => $testresult->iscorrect ? '1': '0');
                        $data[] = $response;
                    }
                }
            }
        }
        return array("response" => $data);
    }

    public static function check_coderunner_answer_returns() {
        return new external_single_structure(
            array(
                'response' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'testcode' => new external_value(PARAM_TEXT, 'Code to Execute'),
                            'expected' => new external_value(PARAM_TEXT, 'Response Expected'),
                            'got' => new external_value(PARAM_TEXT, 'Answer response'),
                            'mark' => new external_value(PARAM_TEXT, 'Marks'),
                            'iscorrect' => new external_value(PARAM_TEXT, 'Is Answer Correct?'),
                        )
                    ), 'CodeRunner API Grader', VALUE_OPTIONAL
                )
            )
        );
    }
}
