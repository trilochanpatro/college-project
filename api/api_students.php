<?php
/**
 * Students API
 * Handles CRUD operations for students
 */

require_once 'db_config.php';

session_start();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

// ==================== GET ALL STUDENTS ====================
if ($method === 'GET' && $action === 'all') {
    $stmt = executeQuery("SELECT * FROM students ORDER BY roll_no ASC");
    
    if (!is_array($stmt)) {
        $students = fetchResults($stmt);
        echo json_encode(['status' => 'success', 'data' => $students]);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== GET STUDENT BY ID ====================
elseif ($method === 'GET' && $action === 'get' && !empty($id)) {
    $stmt = executeQuery("SELECT * FROM students WHERE id = ?", "i", [$id]);
    
    if (!is_array($stmt)) {
        $student = fetchSingleRow($stmt);
        if (!empty($student)) {
            echo json_encode(['status' => 'success', 'data' => $student]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Student not found']);
        }
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== GET STUDENTS BY SECTION ====================
elseif ($method === 'GET' && $action === 'section') {
    $section = $_GET['section'] ?? '';
    $semester = $_GET['semester'] ?? '';
    
    $query = "SELECT * FROM students WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($section)) {
        $query .= " AND section = ?";
        $params[] = $section;
        $types .= "s";
    }
    
    if (!empty($semester)) {
        $query .= " AND semester = ?";
        $params[] = $semester;
        $types .= "i";
    }
    
    $query .= " ORDER BY roll_no ASC";
    
    $stmt = executeQuery($query, $types, $params);
    
    if (!is_array($stmt)) {
        $students = fetchResults($stmt);
        echo json_encode(['status' => 'success', 'data' => $students]);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== CREATE STUDENT ====================
elseif ($method === 'POST' && $action === 'create') {
    // Check admin/faculty role
    if (!in_array($_SESSION['role'], ['admin', 'faculty'])) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
        exit();
    }
    
    $input = json_decode(file_get_contents("php://input"), true);
    
    $required = ['roll_no', 'name', 'dept_id', 'course_id', 'semester', 'section', 'email'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => "Missing required field: $field"]);
            exit();
        }
    }
    
    $stmt = executeQuery(
        "INSERT INTO students (user_id, roll_no, name, dept_id, course_id, semester, section, email, contact) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
        "issiiisss",
        [
            $input['user_id'] ?? null,
            $input['roll_no'],
            $input['name'],
            $input['dept_id'],
            $input['course_id'],
            $input['semester'],
            $input['section'],
            $input['email'],
            $input['contact'] ?? null
        ]
    );
    
    if (!is_array($stmt)) {
        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Student created successfully',
            'id' => mysqli_insert_id($GLOBALS['mysqli'])
        ]);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== UPDATE STUDENT ====================
elseif ($method === 'PUT' && $action === 'update' && !empty($id)) {
    // Check admin/faculty role
    if (!in_array($_SESSION['role'], ['admin', 'faculty'])) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
        exit();
    }
    
    $input = json_decode(file_get_contents("php://input"), true);
    
    // Build dynamic update query
    $updates = [];
    $params = [];
    $types = "";
    
    $allowed_fields = ['user_id', 'roll_no', 'name', 'dept_id', 'course_id', 'semester', 'section', 'email', 'contact'];
    
    foreach ($allowed_fields as $field) {
        if (isset($input[$field])) {
            $updates[] = "$field = ?";
            $params[] = $input[$field];
            $types .= in_array($field, ['dept_id', 'course_id', 'semester', 'user_id']) ? 'i' : 's';
        }
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
        exit();
    }
    
    $params[] = $id;
    $types .= "i";
    
    $query = "UPDATE students SET " . implode(", ", $updates) . " WHERE id = ?";
    
    $stmt = executeQuery($query, $types, $params);
    
    if (!is_array($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Student updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== DELETE STUDENT ====================
elseif ($method === 'DELETE' && $action === 'delete' && !empty($id)) {
    // Check admin role only
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Only admin can delete students']);
        exit();
    }
    
    $stmt = executeQuery("DELETE FROM students WHERE id = ?", "i", [$id]);
    
    if (!is_array($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Student deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
