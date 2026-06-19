<?php
/**
 * Attendance API
 * Handles attendance sessions and records
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

// ==================== CREATE ATTENDANCE SESSION ====================
if ($method === 'POST' && $action === 'create-session') {
    // Check faculty/admin role
    if (!in_array($_SESSION['role'], ['admin', 'faculty'])) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
        exit();
    }
    
    $input = json_decode(file_get_contents("php://input"), true);
    
    $required = ['faculty_id', 'subject_id', 'section', 'session_date'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => "Missing required field: $field"]);
            exit();
        }
    }
    
    // Get student count for the section
    $stmt = executeQuery(
        "SELECT COUNT(*) as count FROM students WHERE section = ? AND semester = (SELECT semester FROM subjects WHERE id = ?)",
        "si",
        [$input['section'], $input['subject_id']]
    );
    
    $count_result = fetchSingleRow($stmt);
    $total_students = $count_result['count'] ?? 0;
    
    $stmt = executeQuery(
        "INSERT INTO attendance_sessions (faculty_id, subject_id, section, session_date, session_time, total_students) 
         VALUES (?, ?, ?, ?, ?, ?)",
        "iisssi",
        [
            $input['faculty_id'],
            $input['subject_id'],
            $input['section'],
            $input['session_date'],
            $input['session_time'] ?? null,
            $total_students
        ]
    );
    
    if (!is_array($stmt)) {
        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Attendance session created',
            'session_id' => mysqli_insert_id($GLOBALS['mysqli']),
            'total_students' => $total_students
        ]);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== MARK ATTENDANCE ====================
elseif ($method === 'POST' && $action === 'mark-attendance') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    $required = ['session_id', 'student_id', 'attendance_status'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => "Missing required field: $field"]);
            exit();
        }
    }
    
    $stmt = executeQuery(
        "INSERT INTO attendance_details (session_id, student_id, attendance_status, remarks) 
         VALUES (?, ?, ?, ?) 
         ON DUPLICATE KEY UPDATE attendance_status = VALUES(attendance_status), remarks = VALUES(remarks)",
        "iiss",
        [
            $input['session_id'],
            $input['student_id'],
            $input['attendance_status'],
            $input['remarks'] ?? null
        ]
    );
    
    if (!is_array($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Attendance marked successfully']);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== GET ATTENDANCE RECORD ====================
elseif ($method === 'GET' && $action === 'record') {
    $session_id = $_GET['session_id'] ?? '';
    
    if (empty($session_id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'session_id required']);
        exit();
    }
    
    $stmt = executeQuery(
        "SELECT ad.*, s.roll_no, s.name 
         FROM attendance_details ad
         JOIN students s ON ad.student_id = s.id
         WHERE ad.session_id = ?
         ORDER BY s.roll_no ASC",
        "i",
        [$session_id]
    );
    
    if (!is_array($stmt)) {
        $records = fetchResults($stmt);
        echo json_encode(['status' => 'success', 'data' => $records]);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== GET ATTENDANCE REPORT ====================
elseif ($method === 'GET' && $action === 'report') {
    $student_id = $_GET['student_id'] ?? '';
    $subject_id = $_GET['subject_id'] ?? '';
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    
    $query = "SELECT s.roll_no, st.name, COUNT(*) as total_classes, 
              SUM(CASE WHEN ad.attendance_status = 'present' THEN 1 ELSE 0 END) as present,
              SUM(CASE WHEN ad.attendance_status = 'absent' THEN 1 ELSE 0 END) as absent,
              SUM(CASE WHEN ad.attendance_status = 'late' THEN 1 ELSE 0 END) as late,
              SUM(CASE WHEN ad.attendance_status = 'excused' THEN 1 ELSE 0 END) as excused,
              ROUND(SUM(CASE WHEN ad.attendance_status = 'present' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as percentage
              FROM attendance_details ad
              JOIN attendance_sessions s ON ad.session_id = s.id
              JOIN students st ON ad.student_id = st.id
              WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if (!empty($student_id)) {
        $query .= " AND ad.student_id = ?";
        $params[] = $student_id;
        $types .= "i";
    }
    
    if (!empty($subject_id)) {
        $query .= " AND s.subject_id = ?";
        $params[] = $subject_id;
        $types .= "i";
    }
    
    if (!empty($start_date)) {
        $query .= " AND s.session_date >= ?";
        $params[] = $start_date;
        $types .= "s";
    }
    
    if (!empty($end_date)) {
        $query .= " AND s.session_date <= ?";
        $params[] = $end_date;
        $types .= "s";
    }
    
    $query .= " GROUP BY ad.student_id, st.name, st.roll_no";
    
    $stmt = executeQuery($query, $types, $params);
    
    if (!is_array($stmt)) {
        $report = fetchResults($stmt);
        echo json_encode(['status' => 'success', 'data' => $report]);
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== GET SESSIONS FOR FACULTY ====================
elseif ($method === 'GET' && $action === 'sessions') {
    $faculty_id = $_GET['faculty_id'] ?? '';
    
    if (empty($faculty_id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'faculty_id required']);
        exit();
    }
    
    $stmt = executeQuery(
        "SELECT s.*, sub.code as subject_code, sub.name as subject_name 
         FROM attendance_sessions s
         JOIN subjects sub ON s.subject_id = sub.id
         WHERE s.faculty_id = ?
         ORDER BY s.session_date DESC, s.session_time DESC",
        "i",
        [$faculty_id]
    );
    
    if (!is_array($stmt)) {
        $sessions = fetchResults($stmt);
        echo json_encode(['status' => 'success', 'data' => $sessions]);
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
