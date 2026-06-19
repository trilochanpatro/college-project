<?php
/**
 * Database Configuration File
 * Handles all database connections for the Attendance Management System
 */

// Database credentials
define('DB_SERVER', 'localhost');      // Your MySQL server address
define('DB_USER', 'root');             // MySQL username (default: root)
define('DB_PASSWORD', '');             // MySQL password (leave empty for default)
define('DB_NAME', 'attendance_system'); // Database name

// Create connection
$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $mysqli->connect_error
    ]));
}

// Set charset to UTF-8
$mysqli->set_charset("utf8mb4");

// Define response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Function to execute queries safely
 * 
 * @param string $sql SQL query
 * @param string $types Parameter types (i=int, s=string, d=double, b=blob)
 * @param array $params Parameters to bind
 * @return mixed Result or error
 */
function executeQuery($sql, $types = '', $params = []) {
    global $mysqli;
    
    try {
        if (!empty($params)) {
            $stmt = $mysqli->prepare($sql);
            if (!$stmt) {
                return ['status' => 'error', 'message' => 'Prepare failed: ' . $mysqli->error];
            }
            
            if (!$stmt->bind_param($types, ...$params)) {
                return ['status' => 'error', 'message' => 'Binding parameters failed: ' . $stmt->error];
            }
            
            if (!$stmt->execute()) {
                return ['status' => 'error', 'message' => 'Execute failed: ' . $stmt->error];
            }
            
            return $stmt;
        } else {
            return $mysqli->query($sql);
        }
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

/**
 * Function to fetch results as associative array
 * 
 * @param object $stmt Statement object
 * @return array Results
 */
function fetchResults($stmt) {
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Function to fetch single row
 * 
 * @param object $stmt Statement object
 * @return array Single row or empty array
 */
function fetchSingleRow($stmt) {
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?? [];
}
?>
