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
 * External API for HRIS Integration
 *
 * @package    local_hris
 * @copyright  2025 Prihantoosa <pht854@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * HRIS external functions
 */
class local_hris_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_active_courses_parameters() {
        return new external_function_parameters([
            'apikey' => new external_value(PARAM_TEXT, 'API key for authentication')
        ]);
    }

    /**
     * Get list of active courses
     * @param string $apikey API key
     * @return array List of active courses
     */
    public static function get_active_courses($apikey) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::get_active_courses_parameters(), [
            'apikey' => $apikey
        ]);

        // Validate API key
        if (!self::validate_api_key($params['apikey'])) {
            throw new moodle_exception('invalidapikey', 'local_hris');
        }

        // Get context
        $context = context_system::instance();
        self::validate_context($context);

        // Get active courses (exclude site course)
        $sql = "SELECT c.id, c.shortname, c.fullname, c.summary, c.startdate, c.enddate, c.visible
                FROM {course} c 
                WHERE c.id != :siteid 
                AND c.visible = 1
                ORDER BY c.fullname";
        
        $courses = $DB->get_records_sql($sql, ['siteid' => SITEID]);
        
        $result = [];
        foreach ($courses as $course) {
            $result[] = [
                'id' => $course->id,
                'shortname' => $course->shortname,
                'fullname' => $course->fullname,
                'summary' => strip_tags($course->summary),
                'startdate' => $course->startdate,
                'enddate' => $course->enddate,
                'visible' => $course->visible
            ];
        }

        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_active_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Course ID'),
                'shortname' => new external_value(PARAM_TEXT, 'Course short name'),
                'fullname' => new external_value(PARAM_TEXT, 'Course full name'),
                'summary' => new external_value(PARAM_TEXT, 'Course summary'),
                'startdate' => new external_value(PARAM_INT, 'Course start date'),
                'enddate' => new external_value(PARAM_INT, 'Course end date'),
                'visible' => new external_value(PARAM_INT, 'Course visibility')
            ])
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_course_participants_parameters() {
        return new external_function_parameters([
            'apikey' => new external_value(PARAM_TEXT, 'API key for authentication'),
            'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_OPTIONAL, 0)
        ]);
    }

    /**
     * Get participants in courses
     * @param string $apikey API key
     * @param int $courseid Course ID (0 for all courses)
     * @return array List of participants
     */
    public static function get_course_participants($apikey, $courseid = 0) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::get_course_participants_parameters(), [
            'apikey' => $apikey,
            'courseid' => $courseid
        ]);

        // Validate API key
        if (!self::validate_api_key($params['apikey'])) {
            throw new moodle_exception('invalidapikey', 'local_hris');
        }

        // Get context
        $context = context_system::instance();
        self::validate_context($context);

        // Build SQL based on course filter
        $sql = "SELECT DISTINCT u.id, u.email, u.firstname, u.lastname, 
                       COALESCE(uif_company.data, '') as company_name,
                       c.id as course_id, c.shortname, c.fullname as course_name,
                       ue.timecreated as enrollment_date
                FROM {user} u
                JOIN {user_enrolments} ue ON u.id = ue.userid
                JOIN {enrol} e ON ue.enrolid = e.id
                JOIN {course} c ON e.courseid = c.id
                LEFT JOIN {user_info_field} uif_company ON uif_company.shortname = 'branch'
                LEFT JOIN {user_info_data} uif_company ON u.id = uif_company.userid AND uif_company.fieldid = uif_company.id
                WHERE u.deleted = 0 
                AND u.confirmed = 1
                AND c.id != :siteid
                AND c.visible = 1";

        $sqlparams = ['siteid' => SITEID];

        if ($params['courseid'] > 0) {
            $sql .= " AND c.id = :courseid";
            $sqlparams['courseid'] = $params['courseid'];
        }

        $sql .= " ORDER BY c.fullname, u.lastname, u.firstname";

        $participants = $DB->get_records_sql($sql, $sqlparams);

        $result = [];
        foreach ($participants as $participant) {
            $result[] = [
                'user_id' => $participant->id,
                'email' => $participant->email,
                'firstname' => $participant->firstname,
                'lastname' => $participant->lastname,
                'company_name' => $participant->company_name ?: '',
                'course_id' => $participant->course_id,
                'course_shortname' => $participant->shortname,
                'course_name' => $participant->course_name,
                'enrollment_date' => $participant->enrollment_date
            ];
        }

        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_course_participants_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'user_id' => new external_value(PARAM_INT, 'User ID'),
                'email' => new external_value(PARAM_EMAIL, 'User email'),
                'firstname' => new external_value(PARAM_TEXT, 'User first name'),
                'lastname' => new external_value(PARAM_TEXT, 'User last name'),
                'company_name' => new external_value(PARAM_TEXT, 'Company name'),
                'course_id' => new external_value(PARAM_INT, 'Course ID'),
                'course_shortname' => new external_value(PARAM_TEXT, 'Course short name'),
                'course_name' => new external_value(PARAM_TEXT, 'Course name'),
                'enrollment_date' => new external_value(PARAM_INT, 'Enrollment date')
            ])
        );
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_course_results_parameters() {
        return new external_function_parameters([
            'apikey' => new external_value(PARAM_TEXT, 'API key for authentication'),
            'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_OPTIONAL, 0),
            'userid' => new external_value(PARAM_INT, 'User ID', VALUE_OPTIONAL, 0)
        ]);
    }

    /**
     * Get course results with pre-test and post-test scores
     * @param string $apikey API key
     * @param int $courseid Course ID (0 for all courses)
     * @param int $userid User ID (0 for all users)
     * @return array List of course results
     */
    public static function get_course_results($apikey, $courseid = 0, $userid = 0) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::get_course_results_parameters(), [
            'apikey' => $apikey,
            'courseid' => $courseid,
            'userid' => $userid
        ]);

        // Validate API key
        if (!self::validate_api_key($params['apikey'])) {
            throw new moodle_exception('invalidapikey', 'local_hris');
        }

        // Get context
        $context = context_system::instance();
        self::validate_context($context);

        // Get quiz module ID
        $quizmodule = $DB->get_record('modules', ['name' => 'quiz']);
        if (!$quizmodule) {
            return [];
        }

        // Build SQL based on filters
        $sql = "SELECT
                    u.id AS user_id,
                    u.email AS email,
                    u.firstname,
                    u.lastname,
                    COALESCE(uid.data, '') as company_name,
                    c.id as course_id,
                    c.shortname,
                    c.fullname AS course_name,
                    cc.timecompleted,
                    ROUND(MAX(CASE WHEN mcd.value = '2' THEN gg.finalgrade END), 2) AS pretest_score,
                    ROUND(MAX(CASE WHEN mcd.value = '3' THEN gg.finalgrade END), 2) AS posttest_score,
                    ROUND(MAX(ggg.finalgrade), 2) as final_grade
                FROM {user} u
                JOIN {user_enrolments} ue ON ue.userid = u.id
                JOIN {enrol} e ON e.id = ue.enrolid
                JOIN {course} c ON c.id = e.courseid
                LEFT JOIN {course_modules} mcm ON mcm.course = c.id AND mcm.module = :moduleid
                LEFT JOIN {customfield_data} mcd ON mcd.instanceid = mcm.id AND mcd.value IN ('2', '3')
                LEFT JOIN {grade_items} gi ON gi.iteminstance = mcm.instance AND gi.itemmodule = 'quiz'
                LEFT JOIN {grade_grades} gg ON gg.userid = u.id AND gg.itemid = gi.id
                LEFT JOIN {grade_items} ggi ON ggi.courseid = c.id AND ggi.itemtype = 'course'
                LEFT JOIN {grade_grades} ggg ON ggg.userid = u.id AND ggg.itemid = ggi.id
                LEFT JOIN {course_completions} cc ON cc.userid = u.id AND cc.course = c.id
                LEFT JOIN {user_info_field} uif ON uif.shortname = 'branch'
                LEFT JOIN {user_info_data} uid ON uid.userid = u.id AND uid.fieldid = uif.id
                WHERE u.deleted = 0
                AND u.confirmed = 1
                AND c.id != :siteid
                AND c.visible = 1
                AND EXISTS (
                    SELECT 1
                    FROM {role_assignments} ra
                    JOIN {context} ctx ON ctx.id = ra.contextid
                    WHERE ra.userid = u.id
                    AND ctx.instanceid = c.id
                    AND ctx.contextlevel = 50
                    AND ra.roleid = 5
                )";

        $sqlparams = [
            'moduleid' => $quizmodule->id,
            'siteid' => SITEID
        ];

        if ($params['courseid'] > 0) {
            $sql .= " AND c.id = :courseid";
            $sqlparams['courseid'] = $params['courseid'];
        }

        if ($params['userid'] > 0) {
            $sql .= " AND u.id = :userid";
            $sqlparams['userid'] = $params['userid'];
        }

        $sql .= " GROUP BY u.id, u.email, u.firstname, u.lastname, uid.data, c.id, c.shortname, c.fullname, cc.timecompleted
                  ORDER BY c.fullname, u.lastname, u.firstname";

        $results = $DB->get_records_sql($sql, $sqlparams);

        $final_results = [];
        foreach ($results as $result) {
            $final_results[] = [
                'user_id' => $result->user_id,
                'email' => $result->email,
                'firstname' => $result->firstname,
                'lastname' => $result->lastname,
                'company_name' => $result->company_name ?: '',
                'course_id' => $result->course_id,
                'course_shortname' => $result->shortname,
                'course_name' => $result->course_name,
                'final_grade' => $result->final_grade ?: 0.00,
                'pretest_score' => $result->pretest_score ?: 0.00,
                'posttest_score' => $result->posttest_score ?: 0.00,
                'completion_date' => $result->timecompleted ?: 0,
                'is_completed' => $result->timecompleted ? 1 : 0
            ];
        }

        return $final_results;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_course_results_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'user_id' => new external_value(PARAM_INT, 'User ID'),
                'email' => new external_value(PARAM_EMAIL, 'User email'),
                'firstname' => new external_value(PARAM_TEXT, 'User first name'),
                'lastname' => new external_value(PARAM_TEXT, 'User last name'),
                'company_name' => new external_value(PARAM_TEXT, 'Company name'),
                'course_id' => new external_value(PARAM_INT, 'Course ID'),
                'course_shortname' => new external_value(PARAM_TEXT, 'Course short name'),
                'course_name' => new external_value(PARAM_TEXT, 'Course name'),
                'final_grade' => new external_value(PARAM_FLOAT, 'Final grade'),
                'pretest_score' => new external_value(PARAM_FLOAT, 'Pre-test score'),
                'posttest_score' => new external_value(PARAM_FLOAT, 'Post-test score'),
                'completion_date' => new external_value(PARAM_INT, 'Course completion date'),
                'is_completed' => new external_value(PARAM_INT, 'Is course completed')
            ])
        );
    }

    /**
     * Validate API key
     * @param string $apikey API key to validate
     * @return bool True if valid, false otherwise
     */
    private static function validate_api_key($apikey) {
        $stored_key = get_config('local_hris', 'api_key');
        return !empty($stored_key) && $apikey === $stored_key;
    }
}