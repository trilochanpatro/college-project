<?php
/**
 * Faculty API
 * Handles CRUD operations for faculty members
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

// ==================== GET ALL FACULTY ====================
if ($method === 'GET' && $action === 'all') {
    $stmt = executeQuery(
        "SELECT f.*, d.name as dept_name, u.email FROM faculty f 
         JOIN departments d ON f.dept_id = d.id
         JOIN users u ON f.user_id = u.id
         ORDER BY f.name ASC"
    );
    
    if (!is_array($stmt)) {
        $faculty = fetchResults($stmt);
        echo json_encode(['status' => 'success', 'data' => $faculty]);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== GET FACULTY BY ID ====================
elseif ($method === 'GET' && $action === 'get' && !empty($id)) {
    $stmt = executeQuery(
        "SELECT f.*, d.name as dept_name, u.email FROM faculty f 
         JOIN departments d ON f.dept_id = d.id
         JOIN users u ON f.user_id = u.id
         WHERE f.id = ?",
        "i",
        [$id]
    );
    
    if (!is_array($stmt)) {
        $faculty = fetchSingleRow($stmt);
        if (!empty($faculty)) {
            echo json_encode(['status' => 'success', 'data' => $faculty]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Faculty not found']);
        }
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== GET FACULTY BY DEPARTMENT ====================
elseif ($method === 'GET' && $action === 'department') {
    $dept_id = $_GET['dept_id'] ?? '';
    
    if (empty($dept_id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'dept_id required']);
        exit();
    }
    
    $stmt = executeQuery(
        "SELECT f.*, d.name as dept_name, u.email FROM faculty f 
         JOIN departments d ON f.dept_id = d.id
         JOIN users u ON f.user_id = u.id
         WHERE f.dept_id = ?
         ORDER BY f.name ASC",
        "i",
        [$dept_id]
    );
    
    if (!is_array($stmt)) {
        $faculty = fetchResults($stmt);
        echo json_encode(['status' => 'success', 'data' => $faculty]);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== CREATE FACULTY ====================
elseif ($method === 'POST' && $action === 'create') {
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Only admin can create faculty']);
        exit();
    }
    
    $input = json_decode(file_get_contents("php://input"), true);
    
    $required = ['user_id', 'name', 'dept_id'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => "Missing required field: $field"]);
            exit();
        }
    }
    
    $stmt = executeQuery(
        "INSERT INTO faculty (user_id, name, dept_id, designation, qualification, contact) 
         VALUES (?, ?, ?, ?, ?, ?)",
        "isssss",
        [
            $input['user_id'],
            $input['name'],
            $input['dept_id'],
            $input['designation'] ?? null,
            $input['qualification'] ?? null,
            $input['contact'] ?? null
        ]
    );
    
    if (!is_array($stmt)) {
        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Faculty created successfully',
            'id' => mysqli_insert_id($GLOBALS['mysqli'])
        ]);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== UPDATE FACULTY ====================
elseif ($method === 'PUT' && $action === 'update' && !empty($id)) {
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Only admin can update faculty']);
        exit();
    }
    
    $input = json_decode(file_get_contents("php://input"), true);
    
    $updates = [];
    $params = [];
    $types = "";
    
    $allowed_fields = ['name', 'dept_id', 'designation', 'qualification', 'contact'];
    
    foreach ($allowed_fields as $field) {
        if (isset($input[$field])) {
            $updates[] = "$field = ?";
            $params[] = $input[$field];
            $types .= $field === 'dept_id' ? 'i' : 's';
        }
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
        exit();
    }
    
    $params[] = $id;
    $types .= "i";
    
    $query = "UPDATE faculty SET " . implode(", ", $updates) . " WHERE id = ?";
    
    $stmt = executeQuery($query, $types, $params);
    
    if (!is_array($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Faculty updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== DELETE FACULTY ====================
elseif ($method === 'DELETE' && $action === 'delete' && !empty($id)) {
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Only admin can delete faculty']);
        exit();
    }
    
    $stmt = executeQuery("DELETE FROM faculty WHERE id = ?", "i", [$id]);
    
    if (!is_array($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Faculty deleted successfully']);
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
