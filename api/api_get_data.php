<?php
/**
 * Public Data Fetch API
 * Returns all data without authentication
 * Used for frontend initialization
 */

require_once 'db_config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getStudents':
            $result = $mysqli->query("SELECT id, user_id as userId, roll_no as rollNo, name, dept_id as deptId, course_id as courseId, semester, section, email FROM students ORDER BY roll_no ASC");
            $students = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $row['id'] = (int)$row['id'];
                    $row['userId'] = (int)$row['userId'];
                    $row['deptId'] = (int)$row['deptId'];
                    $row['courseId'] = (int)$row['courseId'];
                    $row['semester'] = (int)$row['semester'];
                    $students[] = $row;
                }
            }
            echo json_encode(['status' => 'success', 'data' => $students]);
            break;

        case 'getFaculty':
            $result = $mysqli->query("SELECT id, user_id as userId, name, dept_id as deptId, contact, designation, qualification FROM faculty ORDER BY name ASC");
            $faculty = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $row['id'] = (int)$row['id'];
                    $row['userId'] = (int)$row['userId'];
                    $row['deptId'] = (int)$row['deptId'];
                    $faculty[] = $row;
                }
            }
            echo json_encode(['status' => 'success', 'data' => $faculty]);
            break;

        case 'getDepartments':
            $result = $mysqli->query("SELECT id, name, description FROM departments ORDER BY name ASC");
            $depts = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $row['id'] = (int)$row['id'];
                    $depts[] = $row;
                }
            }
            echo json_encode(['status' => 'success', 'data' => $depts]);
            break;

        case 'getCourses':
            $result = $mysqli->query("SELECT id, name, dept_id as deptId FROM courses ORDER BY name ASC");
            $courses = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $row['id'] = (int)$row['id'];
                    $row['deptId'] = (int)$row['deptId'];
                    $courses[] = $row;
                }
            }
            echo json_encode(['status' => 'success', 'data' => $courses]);
            break;

        case 'getSubjects':
            $result = $mysqli->query("SELECT id, code, name, course_id as courseId, semester FROM subjects ORDER BY code ASC");
            $subjects = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $row['id'] = (int)$row['id'];
                    $row['courseId'] = (int)$row['courseId'];
                    $row['semester'] = (int)$row['semester'];
                    $subjects[] = $row;
                }
            }
            echo json_encode(['status' => 'success', 'data' => $subjects]);
            break;

        case 'getAllocations':
            $result = $mysqli->query("SELECT id, faculty_id as facultyId, subject_id as subjectId, semester, section, academic_year as academicYear FROM subject_allocations ORDER BY id ASC");
            $allocs = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $row['id'] = (int)$row['id'];
                    $row['facultyId'] = (int)$row['facultyId'];
                    $row['subjectId'] = (int)$row['subjectId'];
                    $row['semester'] = (int)$row['semester'];
                    $allocs[] = $row;
                }
            }
            echo json_encode(['status' => 'success', 'data' => $allocs]);
            break;

        case 'getSessions':
            $result = $mysqli->query("SELECT id, faculty_id as facultyId, subject_id as subjectId, section, session_date as sessionDate, session_time as sessionTime, total_students as totalStudents FROM attendance_sessions ORDER BY session_date DESC, session_time ASC");
            $sessions = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    // Convert IDs to integers for proper comparison in JavaScript
                    $row['id'] = (int)$row['id'];
                    $row['facultyId'] = (int)$row['facultyId'];
                    $row['subjectId'] = (int)$row['subjectId'];
                    $row['totalStudents'] = (int)$row['totalStudents'];
                    $sessions[] = $row;
                }
            }
            echo json_encode(['status' => 'success', 'data' => $sessions]);
            break;

        case 'getDetails':
            $result = $mysqli->query("SELECT id, session_id as sessionId, student_id as studentId, attendance_status as status, remarks as remark FROM attendance_details ORDER BY session_id ASC, student_id ASC");
            $details = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    // Convert IDs to integers for proper comparison in JavaScript
                    $row['id'] = (int)$row['id'];
                    $row['sessionId'] = (int)$row['sessionId'];
                    $row['studentId'] = (int)$row['studentId'];
                    $details[] = $row;
                }
            }
            echo json_encode(['status' => 'success', 'data' => $details]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
