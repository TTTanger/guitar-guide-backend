<?php
// 允许指定来源跨域请求
header("Access-Control-Allow-Origin: https://www.guitar-guide.org");

// 如果你想允许所有来源（不推荐，安全风险），写成：
// header("Access-Control-Allow-Origin: *");

// 允许请求方法
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// 允许请求头
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
// 设置 session cookie 参数，支持跨域、SameSite=None、secure
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '.guitar-guide.org',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None'
]);
session_start(); // Start the session to access session variables
header('Content-Type: application/json'); // Set response type to JSON
require_once "mysql.php"; // Include database connection
require "decrypt.php"; // Include password decryption function
$id = $_SESSION['id']; // Get user ID from session


// Fetch user profile information from the database
function getUserProfile($id, $conn)
{
    $sql = "SELECT user_name, user_avatar, created_at, best_score FROM accounts WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo json_encode([
                'success' => true,
                'username' => $row['user_name'],
                'user_avatar' => $row['user_avatar'] ?? '../images/default_avatar.jpeg',
                'join_date' => date('F j, Y', strtotime($row['created_at'])),
                'best_score' => $row['best_score']
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'User not found']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    $stmt = null;
}

// Update the user's password after verifying the current password
function updatePassword($id, $time, $conn)
{
    // Validate input
    if (!isset($_POST['current_password']) || !isset($_POST['new_password'])) {
        echo json_encode(['success' => false, 'error' => 'Missing password data']);
        return;
    }

    $current_password = decrypt($_POST['current_password'], $time);
    $new_password = decrypt($_POST['new_password'], $time);

    // Fetch the current password hash from the database
    $sql = "SELECT user_password FROM accounts WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            if ($current_password == $row['user_password']) {
                // Update the user's password in the database
                $update_sql = "UPDATE accounts SET user_password = :new_password WHERE id = :id";
                $stmt2 = $conn->prepare($update_sql);
                $stmt2->bindParam(':new_password', $new_password, PDO::PARAM_STR);
                $stmt2->bindParam(':id', $id, PDO::PARAM_INT);
                if ($stmt2->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to update password']);
                }
                $stmt2 = null;
            } else {
                echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to fetch current password']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare query']);
    }
}

// Handle avatar upload and update the user's avatar path in the database
function postAvatar($id, $conn)
{
    if (!isset($_FILES['avatar'])) {
        echo json_encode(['success' => false, 'error' => 'No file uploaded']);
        return;
    }

    $file = $_FILES['avatar'];

    $tmp_name = $file['tmp_name'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Only allow certain file types for avatars
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($file_ext, $allowedTypes)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file type']);
        return;
    }

    // Generate a unique file name for the uploaded avatar
    $new_file_name = uniqid() . '.' . $file_ext;
    $db_path = $upload_path = '../uploads/avatars/' . $new_file_name;

    // Move the temporary file to the uploads folder
    if (move_uploaded_file($tmp_name, $upload_path)) {
        $sql = "UPDATE accounts SET user_avatar = :avatar WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':avatar', $db_path, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'user_avatar' => $db_path
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database update failed']);
        }
        $stmt = null;
    } else {
        echo json_encode(['success' => false, 'error' => 'File upload failed']);
    }
}

// Determine the requested action (GET or POST)
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Route the request to the appropriate function
switch ($action) {
    case 'getUserProfile':
        getUserProfile($id, $conn);
        break;
    case 'postAvatar':
        postAvatar($id, $conn);
        break;
    case 'updatePassword':
        $time = $_POST['time'];
        updatePassword($id, $time, $conn);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn = null; // Close the PDO connection
?>