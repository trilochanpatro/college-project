<?php
/**
 * Admin API
 * Handles admin-specific operations
 */

require_once 'db_config.php';

session_start();

// Check authentication and admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Admin access required']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ==================== DASHBOARD STATISTICS ====================
if ($method === 'GET' && $action === 'dashboard-stats') {
    
    // Get total counts
    $stmt = executeQuery("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
    $total_students = fetchSingleRow($stmt)['count'] ?? 0;
    
    $stmt = executeQuery("SELECT COUNT(*) as count FROM faculty");
    $total_faculty = fetchSingleRow($stmt)['count'] ?? 0;
    
    $stmt = executeQuery("SELECT COUNT(*) as count FROM subjects");
    $total_subjects = fetchSingleRow($stmt)['count'] ?? 0;
    
    $stmt = executeQuery("SELECT COUNT(*) as count FROM attendance_sessions");
    $total_sessions = fetchSingleRow($stmt)['count'] ?? 0;
    
    // Get today's attendance
    $today = date('Y-m-d');
    $stmt = executeQuery(
        "SELECT COUNT(*) as count FROM attendance_sessions WHERE session_date = ?",
        "s",
        [$today]
    );
    $today_sessions = fetchSingleRow($stmt)['count'] ?? 0;
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_students' => $total_students,
            'total_faculty' => $total_faculty,
            'total_subjects' => $total_subjects,
            'total_sessions' => $total_sessions,
            'today_sessions' => $today_sessions
        ]
    ]);
}

// ==================== GET ALL USERS ====================
elseif ($method === 'GET' && $action === 'users') {
    $role = $_GET['role'] ?? '';
    
    $query = "SELECT id, username, email, role, is_active, created_at FROM users WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($role)) {
        $query .= " AND role = ?";
        $params[] = $role;
        $types .= "s";
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = executeQuery($query, $types, $params);
    
    if (!is_array($stmt)) {
        $users = fetchResults($stmt);
        echo json_encode(['status' => 'success', 'data' => $users]);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== CREATE USER ====================
elseif ($method === 'POST' && $action === 'create-user') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    $required = ['username', 'password', 'email', 'role'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => "Missing required field: $field"]);
            exit();
        }
    }
    
    if (!in_array($input['role'], ['admin', 'faculty', 'student'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid role']);
        exit();
    }
    
    $stmt = executeQuery(
        "INSERT INTO users (username, password, email, role) VALUES (?, SHA2(?, 256), ?, ?)",
        "ssss",
        [
            $input['username'],
            $input['password'],
            $input['email'],
            $input['role']
        ]
    );
    
    if (!is_array($stmt)) {
        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'User created successfully',
            'id' => mysqli_insert_id($GLOBALS['mysqli'])
        ]);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== DEACTIVATE USER ====================
elseif ($method === 'PUT' && $action === 'deactivate-user') {
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'User ID required']);
        exit();
    }
    
    $stmt = executeQuery(
        "UPDATE users SET is_active = FALSE WHERE id = ? AND id != ?",
        "ii",
        [$id, $_SESSION['user_id']]
    );
    
    if (!is_array($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'User deactivated']);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== GET ALL DEPARTMENTS ====================
elseif ($method === 'GET' && $action === 'departments') {
    $stmt = executeQuery("SELECT * FROM departments ORDER BY name ASC");
    
    if (!is_array($stmt)) {
        $depts = fetchResults($stmt);
        echo json_encode(['status' => 'success', 'data' => $depts]);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== GET ALL COURSES ====================
elseif ($method === 'GET' && $action === 'courses') {
    $stmt = executeQuery(
        "SELECT c.*, d.name as dept_name FROM courses c 
         JOIN departments d ON c.dept_id = d.id 
         ORDER BY c.name ASC"
    );
    
    if (!is_array($stmt)) {
        $courses = fetchResults($stmt);
        echo json_encode(['status' => 'success', 'data' => $courses]);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== GET ALL SUBJECTS ====================
elseif ($method === 'GET' && $action === 'subjects') {
    $stmt = executeQuery(
        "SELECT s.*, c.name as course_name FROM subjects s 
         JOIN courses c ON s.course_id = c.id 
         ORDER BY s.code ASC"
    );
    
    if (!is_array($stmt)) {
        $subjects = fetchResults($stmt);
        echo json_encode(['status' => 'success', 'data' => $subjects]);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== ATTENDANCE REPORTS ====================
elseif ($method === 'GET' && $action === 'attendance-reports') {
    $report_type = $_GET['type'] ?? 'overall';
    $start_date = $_GET['start_date'] ?? date('Y-m-01');
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    
    if ($report_type === 'overall') {
        $stmt = executeQuery(
            "SELECT s.roll_no, s.name, COUNT(*) as total_classes,
             SUM(CASE WHEN ad.attendance_status = 'present' THEN 1 ELSE 0 END) as present,
             ROUND(SUM(CASE WHEN ad.attendance_status = 'present' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as percentage
             FROM attendance_details ad
             JOIN attendance_sessions se ON ad.session_id = se.id
             JOIN students s ON ad.student_id = s.id
             WHERE se.session_date BETWEEN ? AND ?
             GROUP BY ad.student_id, s.name, s.roll_no
             ORDER BY percentage DESC",
            "ss",
            [$start_date, $end_date]
        );
    } elseif ($report_type === 'faculty') {
        $stmt = executeQuery(
            "SELECT f.name, COUNT(DISTINCT se.id) as total_sessions,
             COUNT(ad.id) as total_marked
             FROM attendance_sessions se
             JOIN faculty f ON se.faculty_id = f.id
             LEFT JOIN attendance_details ad ON se.id = ad.session_id
             WHERE se.session_date BETWEEN ? AND ?
             GROUP BY se.faculty_id, f.name
             ORDER BY total_sessions DESC",
            "ss",
            [$start_date, $end_date]
        );
    } else {
        $stmt = executeQuery(
            "SELECT sub.code, sub.name, COUNT(se.id) as sessions,
             COUNT(ad.id) as attendances_marked
             FROM subjects sub
             LEFT JOIN attendance_sessions se ON sub.id = se.subject_id
             LEFT JOIN attendance_details ad ON se.id = ad.session_id
             WHERE se.session_date IS NULL OR se.session_date BETWEEN ? AND ?
             GROUP BY se.subject_id, sub.code, sub.name
             ORDER BY sessions DESC",
            "ss",
            [$start_date, $end_date]
        );
    }
    
    if (!is_array($stmt)) {
        $data = fetchResults($stmt);
        echo json_encode(['status' => 'success', 'data' => $data]);
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
