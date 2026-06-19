<?php
/**
 * Student Management API
 * Handles add, edit, delete operations for students
 */

require_once 'db_config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];

// Get action from query string or JSON body
$action = $_GET['action'] ?? '';
$jsonData = null;
if (empty($action) && in_array($method, ['POST', 'PUT', 'DELETE'])) {
    $jsonData = json_decode(file_get_contents('php://input'), true);
    $action = $jsonData['action'] ?? '';
} else if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
    // Still decode JSON for body data even if action is in query string
    $jsonData = json_decode(file_get_contents('php://input'), true);
}

try {
    switch ($action) {
        case 'addStudent':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
                break;
            }
            
            $data = $jsonData ?? [];
            $name = $data['name'] ?? '';
            $rollNo = $data['rollNo'] ?? '';
            $email = $data['email'] ?? '';
            $deptId = $data['deptId'] ?? null;
            $courseId = $data['courseId'] ?? null;
            $semester = $data['semester'] ?? null;
            $section = $data['section'] ?? '';
            
            if (!$name || !$rollNo || !$email || !$deptId || !$courseId || !$semester || !$section) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
                break;
            }
            
            // Check if roll number already exists
            $rollNo = $mysqli->real_escape_string($rollNo);
            $check = $mysqli->query("SELECT id FROM students WHERE roll_no = '{$rollNo}'");
            if ($check->num_rows > 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Roll number already exists']);
                break;
            }
            
            // Create user account first
            $username = $mysqli->real_escape_string(strtolower($rollNo));
            $password = 'default123';
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $email = $mysqli->real_escape_string($email);
            $passwordHash = $mysqli->real_escape_string($passwordHash);
            
            $userQuery = "INSERT INTO users (username, password, email, role) VALUES ('{$username}', '{$passwordHash}', '{$email}', 'student')";
            if (!$mysqli->query($userQuery)) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to create user account: ' . $mysqli->error]);
                break;
            }
            
            $userId = $mysqli->insert_id;
            
            // Add student
            $name = $mysqli->real_escape_string($name);
            $section = $mysqli->real_escape_string($section);
            $studentQuery = "INSERT INTO students (user_id, roll_no, name, dept_id, course_id, semester, section, email) VALUES ({$userId}, '{$rollNo}', '{$name}', {$deptId}, {$courseId}, {$semester}, '{$section}', '{$email}')";
            if ($mysqli->query($studentQuery)) {
                $studentId = $mysqli->insert_id;
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Student added successfully',
                    'data' => [
                        'id' => $studentId,
                        'userId' => $userId,
                        'rollNo' => $rollNo,
                        'name' => $name,
                        'email' => $email,
                        'deptId' => $deptId,
                        'courseId' => $courseId,
                        'semester' => $semester,
                        'section' => $section
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to add student']);
            }
            break;
            
        case 'editStudent':
            if ($method !== 'PUT' && $method !== 'POST') {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
                break;
            }
            
            $data = $jsonData ?? [];
            $id = $data['id'] ?? null;
            $name = $data['name'] ?? '';
            $email = $data['email'] ?? '';
            $deptId = $data['deptId'] ?? null;
            $courseId = $data['courseId'] ?? null;
            $semester = $data['semester'] ?? null;
            $section = $data['section'] ?? '';
            
            if (!$id || !$name || !$email) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
                break;
            }
            
            $query = "UPDATE students SET name = '{$name}', email = '{$email}', dept_id = {$deptId}, course_id = {$courseId}, semester = {$semester}, section = '{$section}' WHERE id = {$id}";
            
            if ($mysqli->query($query)) {
                echo json_encode(['status' => 'success', 'message' => 'Student updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update student']);
            }
            break;
            
        case 'deleteStudent':
            if ($method !== 'DELETE') {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
                break;
            }
            
            $data = $jsonData ?? [];
            $id = $data['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Student ID required']);
                break;
            }
            
            // Get user_id first
            $result = $mysqli->query("SELECT user_id FROM students WHERE id = {$id}");
            if ($result->num_rows === 0) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Student not found']);
                break;
            }
            
            $student = $result->fetch_assoc();
            $userId = $student['user_id'];
            
            // Delete attendance records first
            $mysqli->query("DELETE FROM attendance_details WHERE session_id IN (SELECT id FROM attendance_sessions)");
            
            // Delete student
            if ($mysqli->query("DELETE FROM students WHERE id = {$id}") && $mysqli->query("DELETE FROM users WHERE id = {$userId}")) {
                echo json_encode(['status' => 'success', 'message' => 'Student deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete student']);
            }
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
