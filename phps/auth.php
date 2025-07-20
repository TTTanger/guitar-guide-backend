<?php
// 允许指定来源跨域请求

$allowed_origins = [
    "https://www.guitar-guide.org",
    "https://guitar-guide-frontend-eqogsuppy-tangers-projects.vercel.app"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Credentials: true");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 设置 session cookie 参数，支持跨域、SameSite=None、secure
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '.guitar-guide.org', // 适配主域和所有子域
    'secure' => true, // 生产环境建议 true
    'httponly' => true,
    'samesite' => 'None'
]);
session_start(); // Start the session to access session variables
require_once "mysql.php"; // Include database connection

header('Content-Type: application/json'); // Set response type to JSON

// Prepare the response array with login status and username
$response = array(
    'loggedin' => isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true, // Is the user logged in?
    'username' => isset($_SESSION['username']) ? $_SESSION['username'] : null,    // Username if logged in
);

// If user is logged in, fetch avatar from database
if ($response['loggedin'] && isset($_SESSION['id'])) {
    $sql = "SELECT user_avatar FROM accounts WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $_SESSION['id']); 
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                // If user_avatar is set, use it; otherwise, use default avatar
                $response['user_avatar'] = $row['user_avatar'] ?? '../images/default_avatar.jpeg';
            }
        }
        $stmt->close();
    }
}

// Output the response as JSON for the frontend to use
// This includes login status, username, and avatar if available
echo json_encode($response); // Output the response as JSON
$conn->close(); // Close the database connection
?>