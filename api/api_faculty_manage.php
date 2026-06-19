<?php
/**
 * Faculty Management API
 * Handles add, edit, delete operations for faculty members
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
        case 'addFaculty':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
                break;
            }
            
            $data = $jsonData ?? [];
            $name = $data['name'] ?? '';
            $email = $data['email'] ?? '';
            $contact = $data['contact'] ?? '';
            $deptId = $data['deptId'] ?? null;
            $designation = $data['designation'] ?? 'Faculty';
            $qualification = $data['qualification'] ?? '';
            
            if (!$name || !$deptId) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Name and Department are required']);
                break;
            }
            
            // Create user account first (if email provided)
            $userId = null;
            if (!empty($email)) {
                $email = $mysqli->real_escape_string($email);
                $username = $mysqli->real_escape_string(strtolower(str_replace(' ', '', $name)));
                
                // Check if username already exists
                $check = $mysqli->query("SELECT id FROM users WHERE username = '{$username}'");
                if ($check->num_rows > 0) {
                    $username = $username . '_' . time();
                }
                
                $password = 'faculty123';
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $passwordHash = $mysqli->real_escape_string($passwordHash);
                
                $userQuery = "INSERT INTO users (username, password, email, role) VALUES ('{$username}', '{$passwordHash}', '{$email}', 'faculty')";
                if (!$mysqli->query($userQuery)) {
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => 'Failed to create user account: ' . $mysqli->error]);
                    break;
                }
                
                $userId = $mysqli->insert_id;
            }
            
            // Add faculty
            $name = $mysqli->real_escape_string($name);
            $contact = $mysqli->real_escape_string($contact);
            $designation = $mysqli->real_escape_string($designation);
            $qualification = $mysqli->real_escape_string($qualification);
            
            if ($userId === null) {
                $facultyQuery = "INSERT INTO faculty (name, dept_id, contact, designation, qualification) VALUES ('{$name}', {$deptId}, '{$contact}', '{$designation}', '{$qualification}')";
            } else {
                $facultyQuery = "INSERT INTO faculty (user_id, name, dept_id, contact, designation, qualification) VALUES ({$userId}, '{$name}', {$deptId}, '{$contact}', '{$designation}', '{$qualification}')";
            }
            
            if ($mysqli->query($facultyQuery)) {
                $facultyId = $mysqli->insert_id;
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Faculty added successfully',
                    'data' => [
                        'id' => (int)$facultyId,
                        'userId' => (int)($userId ?? 0),
                        'name' => $name,
                        'email' => $email ?? '',
                        'contact' => $contact,
                        'deptId' => (int)$deptId,
                        'designation' => $designation,
                        'qualification' => $qualification
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to add faculty: ' . $mysqli->error]);
            }
            break;
            
        case 'editFaculty':
            if ($method !== 'PUT' && $method !== 'POST') {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
                break;
            }
            
            $data = $jsonData ?? [];
            $id = $data['id'] ?? null;
            $name = $data['name'] ?? '';
            $email = $data['email'] ?? '';
            $contact = $data['contact'] ?? '';
            $deptId = $data['deptId'] ?? null;
            $designation = $data['designation'] ?? 'Faculty';
            $qualification = $data['qualification'] ?? '';
            
            if (!$id || !$name || !$deptId) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ID, Name and Department are required']);
                break;
            }
            
            // Update faculty
            $name = $mysqli->real_escape_string($name);
            $contact = $mysqli->real_escape_string($contact);
            $designation = $mysqli->real_escape_string($designation);
            $qualification = $mysqli->real_escape_string($qualification);
            
            $updateQuery = "UPDATE faculty SET name = '{$name}', contact = '{$contact}', dept_id = {$deptId}, designation = '{$designation}', qualification = '{$qualification}' WHERE id = {$id}";
            
            if ($mysqli->query($updateQuery)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Faculty updated successfully',
                    'data' => [
                        'id' => (int)$id,
                        'name' => $name,
                        'email' => $email,
                        'contact' => $contact,
                        'deptId' => (int)$deptId,
                        'designation' => $designation,
                        'qualification' => $qualification
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update faculty: ' . $mysqli->error]);
            }
            break;
            
        case 'deleteFaculty':
            if ($method !== 'DELETE' && $method !== 'POST') {
                http_response_code(405);
                echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
                break;
            }
            
            $data = $jsonData ?? $_GET;
            $id = $data['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Faculty ID is required']);
                break;
            }
            
            // Get faculty info first
            $result = $mysqli->query("SELECT user_id FROM faculty WHERE id = {$id}");
            $row = $result->fetch_assoc();
            
            // Delete faculty
            $deleteQuery = "DELETE FROM faculty WHERE id = {$id}";
            
            if ($mysqli->query($deleteQuery)) {
                // Delete associated user if exists
                if ($row && $row['user_id']) {
                    $mysqli->query("DELETE FROM users WHERE id = {$row['user_id']}");
                }
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Faculty deleted successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete faculty: ' . $mysqli->error]);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
