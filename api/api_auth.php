<?php
/**
 * Authentication API
 * Handles user login, logout, and session management
 */

require_once 'db_config.php';

$action = $_GET['action'] ?? '';

// ==================== LOGIN ====================
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Username and password required']);
        exit();
    }
    
    // Check user credentials
    $stmt = executeQuery(
        "SELECT id, username, email, role FROM users WHERE username = ? AND password = SHA2(?, 256) AND is_active = TRUE",
        "ss",
        [$username, $password]
    );
    
    if (!is_array($stmt)) {
        $user = fetchSingleRow($stmt);
        
        if (!empty($user)) {
            // Start session
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful',
                'user' => $user
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        }
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

// ==================== LOGOUT ====================
elseif ($action === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    session_destroy();
    
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
}

// ==================== GET CURRENT USER ====================
elseif ($action === 'current-user' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    session_start();
    
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'status' => 'success',
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'role' => $_SESSION['role']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    }
}

// ==================== CHANGE PASSWORD ====================
elseif ($action === 'change-password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
        exit();
    }
    
    $input = json_decode(file_get_contents("php://input"), true);
    $old_password = $input['old_password'] ?? '';
    $new_password = $input['new_password'] ?? '';
    
    if (empty($old_password) || empty($new_password)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Old and new password required']);
        exit();
    }
    
    // Verify old password
    $stmt = executeQuery(
        "SELECT id FROM users WHERE id = ? AND password = SHA2(?, 256)",
        "is",
        [$_SESSION['user_id'], $old_password]
    );
    
    if (!is_array($stmt)) {
        $result = fetchSingleRow($stmt);
        
        if (!empty($result)) {
            // Update password
            $stmt = executeQuery(
                "UPDATE users SET password = SHA2(?, 256) WHERE id = ?",
                "si",
                [$new_password, $_SESSION['user_id']]
            );
            
            if (!is_array($stmt)) {
                echo json_encode(['status' => 'success', 'message' => 'Password changed successfully']);
            } else {
                http_response_code(500);
                echo json_encode($stmt);
            }
        } else {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid old password']);
        }
    } else {
        http_response_code(500);
        echo json_encode($stmt);
    }
}

else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
