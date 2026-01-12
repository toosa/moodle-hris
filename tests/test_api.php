<?php
/**
 * Test script for HRIS API functions
 * 
 * Run from command line:
 * php local/hris/tests/test_api.php
 */

define('CLI_SCRIPT', true);
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/externallib.php');

// Create a test class that bypasses context validation
class local_hris_external_test {
    
    public static function validate_api_key($apikey) {
        $stored_key = get_config('local_hris', 'api_key');
        return !empty($stored_key) && $apikey === $stored_key;
    }
    
    public static function get_quiz_score($userid, $courseid, $type) {
        global $DB;
        
        // Get customfield_field ID for jenis_quiz
        $field = $DB->get_record('customfield_field', ['shortname' => 'jenis_quiz']);
        if (!$field) {
            return 0.00;
        }

        // Determine which value to look for (2 = PreTest, 3 = PostTest)
        $fieldvalue = $type === 'pre' ? '2' : '3';
        
        $sql = "SELECT MAX(qa.sumgrades) as score
                FROM {quiz_attempts} qa
                JOIN {quiz} q ON qa.quiz = q.id
                JOIN {customfield_data} cfd ON cfd.instanceid = q.id AND cfd.fieldid = :fieldid
                WHERE qa.userid = :userid
                AND q.course = :courseid
                AND cfd.value = :fieldvalue
                AND qa.state = 'finished'";
        
        $result = $DB->get_record_sql($sql, [
            'userid' => $userid,
            'courseid' => $courseid,
            'fieldid' => $field->id,
            'fieldvalue' => $fieldvalue
        ]);
        
        return $result && $result->score ? round($result->score, 2) : 0.00;
    }
    
    public static function test_get_active_courses($apikey) {
        global $DB;
        
        if (!self::validate_api_key($apikey)) {
            throw new Exception('Invalid API key');
        }
        
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
    
    public static function test_get_course_participants($apikey, $courseid = 0) {
        global $DB;
        
        if (!self::validate_api_key($apikey)) {
            throw new Exception('Invalid API key');
        }
        
        $sql = "SELECT DISTINCT u.id, u.email, u.firstname, u.lastname, 
                       COALESCE(uid.data, '') as company_name,
                       c.id as course_id, c.shortname, c.fullname as course_name,
                       ue.timecreated as enrollment_date
                FROM {user} u
                JOIN {user_enrolments} ue ON u.id = ue.userid
                JOIN {enrol} e ON ue.enrolid = e.id
                JOIN {course} c ON e.courseid = c.id
                LEFT JOIN {user_info_field} uif ON uif.shortname = 'branch'
                LEFT JOIN {user_info_data} uid ON u.id = uid.userid AND uid.fieldid = uif.id
                WHERE u.deleted = 0 
                AND u.confirmed = 1
                AND c.id != :siteid
                AND c.visible = 1";
        
        $sqlparams = ['siteid' => SITEID];
        
        if ($courseid > 0) {
            $sql .= " AND c.id = :courseid";
            $sqlparams['courseid'] = $courseid;
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
    
    public static function test_get_course_results($apikey, $courseid = 0, $userid = 0) {
        global $DB;
        
        if (!self::validate_api_key($apikey)) {
            throw new Exception('Invalid API key');
        }
        
        $sql = "SELECT DISTINCT u.id as user_id, u.email, u.firstname, u.lastname,
                       COALESCE(uid.data, '') as company_name,
                       c.id as course_id, c.shortname, c.fullname as course_name,
                       cc.timecompleted,
                       COALESCE(gg.finalgrade, 0) as final_grade
                FROM {user} u
                JOIN {user_enrolments} ue ON u.id = ue.userid
                JOIN {enrol} e ON ue.enrolid = e.id
                JOIN {course} c ON e.courseid = c.id
                LEFT JOIN {course_completions} cc ON u.id = cc.userid AND c.id = cc.course
                LEFT JOIN {grade_items} gi ON c.id = gi.courseid AND gi.itemtype = 'course'
                LEFT JOIN {grade_grades} gg ON u.id = gg.userid AND gi.id = gg.itemid
                LEFT JOIN {user_info_field} uif ON uif.shortname = 'branch'
                LEFT JOIN {user_info_data} uid ON u.id = uid.userid AND uid.fieldid = uif.id
                WHERE u.deleted = 0 
                AND u.confirmed = 1
                AND c.id != :siteid
                AND c.visible = 1";
        
        $sqlparams = ['siteid' => SITEID];
        
        if ($courseid > 0) {
            $sql .= " AND c.id = :courseid";
            $sqlparams['courseid'] = $courseid;
        }
        
        if ($userid > 0) {
            $sql .= " AND u.id = :userid";
            $sqlparams['userid'] = $userid;
        }
        
        $sql .= " ORDER BY c.fullname, u.lastname, u.firstname";
        
        $results = $DB->get_records_sql($sql, $sqlparams);
        
        $final_results = [];
        foreach ($results as $result) {
            $pretest_score = self::get_quiz_score($result->user_id, $result->course_id, 'pre');
            $posttest_score = self::get_quiz_score($result->user_id, $result->course_id, 'post');
            
            $final_results[] = [
                'user_id' => $result->user_id,
                'email' => $result->email,
                'firstname' => $result->firstname,
                'lastname' => $result->lastname,
                'company_name' => $result->company_name ?: '',
                'course_id' => $result->course_id,
                'course_shortname' => $result->shortname,
                'course_name' => $result->course_name,
                'final_grade' => round($result->final_grade, 2),
                'pretest_score' => $pretest_score,
                'posttest_score' => $posttest_score,
                'completion_date' => $result->timecompleted ?: 0,
                'is_completed' => $result->timecompleted ? 1 : 0
            ];
        }
        
        return $final_results;
    }
}

echo "=== HRIS API Test Script ===\n\n";

// Get API key from config
$apikey = get_config('local_hris', 'api_key');

if (empty($apikey)) {
    die("ERROR: API key not configured. Please set it in Site Administration > Plugins > Local plugins > HRIS Integration\n");
}

echo "✓ API Key found: " . substr($apikey, 0, 10) . "...\n\n";

// Test 1: Get Active Courses
echo "--- Test 1: get_active_courses ---\n";
try {
    $courses = local_hris_external_test::test_get_active_courses($apikey);
    echo "✓ Success! Found " . count($courses) . " active courses\n";
    
    if (!empty($courses)) {
        $course = $courses[0];
        echo "  Sample course: {$course['fullname']} (ID: {$course['id']})\n";
        $sample_course_id = $course['id'];
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 2: Get Course Participants (all courses)
echo "--- Test 2: get_course_participants (all courses) ---\n";
try {
    $participants = local_hris_external_test::test_get_course_participants($apikey, 0);
    echo "✓ Success! Found " . count($participants) . " participants\n";
    
    if (!empty($participants)) {
        $p = $participants[0];
        echo "  Sample participant:\n";
        echo "    Name: {$p['firstname']} {$p['lastname']}\n";
        echo "    Email: {$p['email']}\n";
        echo "    Company: {$p['company_name']}\n";
        echo "    Course: {$p['course_name']}\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Get Course Participants (specific course)
if (isset($sample_course_id)) {
    echo "--- Test 3: get_course_participants (course ID: $sample_course_id) ---\n";
    try {
        $participants = local_hris_external_test::test_get_course_participants($apikey, $sample_course_id);
        echo "✓ Success! Found " . count($participants) . " participants in this course\n";
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Test 4: Get Course Results (all)
echo "--- Test 4: get_course_results (all) ---\n";
try {
    $results = local_hris_external_test::test_get_course_results($apikey, 0, 0);
    echo "✓ Success! Found " . count($results) . " results\n";
    
    if (!empty($results)) {
        $r = $results[0];
        echo "  Sample result:\n";
        echo "    Name: {$r['firstname']} {$r['lastname']}\n";
        echo "    Email: {$r['email']}\n";
        echo "    Company: {$r['company_name']}\n";
        echo "    Course: {$r['course_name']}\n";
        echo "    Final Grade: {$r['final_grade']}\n";
        echo "    Pre-test: {$r['pretest_score']}\n";
        echo "    Post-test: {$r['posttest_score']}\n";
        echo "    Completed: " . ($r['is_completed'] ? 'Yes' : 'No') . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Verify branch data in database
echo "--- Test 5: Verify branch data in database ---\n";
$sql = "SELECT COUNT(*) as total 
        FROM {user_info_data} uid
        JOIN {user_info_field} uif ON uid.fieldid = uif.id
        WHERE uif.shortname = 'branch' AND uid.data IS NOT NULL AND uid.data != ''";
$count = $DB->count_records_sql($sql);
echo "✓ Found {$count} users with branch/company data\n";

if ($count > 0) {
    $sql = "SELECT u.firstname, u.lastname, uid.data as branch
            FROM {user} u
            JOIN {user_info_data} uid ON u.id = uid.userid
            JOIN {user_info_field} uif ON uid.fieldid = uif.id
            WHERE uif.shortname = 'branch' AND uid.data IS NOT NULL AND uid.data != ''
            LIMIT 3";
    $users = $DB->get_records_sql($sql);
    echo "  Sample users:\n";
    foreach ($users as $user) {
        echo "    - {$user->firstname} {$user->lastname}: {$user->branch}\n";
    }
}
echo "\n";

// Test 6: Check quiz custom field configuration
echo "--- Test 6: Check quiz custom field (jenis_quiz) ---\n";
$field = $DB->get_record('customfield_field', ['shortname' => 'jenis_quiz']);
if ($field) {
    echo "✓ Custom field 'jenis_quiz' found (ID: {$field->id})\n";
    $sql = "SELECT q.id, q.name, cd.value as jenis
            FROM {quiz} q
            JOIN {customfield_data} cd ON cd.instanceid = q.id AND cd.fieldid = :fieldid
            WHERE cd.value IN ('2', '3')
            LIMIT 5";
    $quizzes = $DB->get_records_sql($sql, ['fieldid' => $field->id]);
    if (!empty($quizzes)) {
        echo "  Found quizzes with PreTest/PostTest:\n";
        foreach ($quizzes as $quiz) {
            $type = $quiz->jenis == '2' ? 'PreTest' : 'PostTest';
            echo "    - {$quiz->name} ({$type})\n";
        }
    } else {
        echo "  ⚠ No quizzes marked as PreTest or PostTest\n";
    }
} else {
    echo "✗ Custom field 'jenis_quiz' NOT FOUND\n";
}
echo "\n";

echo "=== Test Complete ===\n";
