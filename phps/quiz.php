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
header('Content-Type: application/json'); // Set response type to JSON
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
require_once 'mysql.php'; // Include database connection

$score = $_POST['score']; // Get score from POST data
$id = $_SESSION['id']; // Get user ID from session

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    echo "You must be logged in to take the quiz";
    exit;
}

// Check if the score is provided
if(!isset($_POST['score'])) {
    echo json_encode(['success' => false, 'error' => 'No score provided']);
    exit;
}

// Update best_score only if new score is higher
// GREATEST(COALESCE(best_score, 0), ?) ensures best_score is never null and always the highest
$sql = "UPDATE accounts SET best_score = GREATEST(COALESCE(best_score, 0), :score) WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':score', $score, PDO::PARAM_INT);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Score updated successfully',
        'score' => $score
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update score']);
}
$stmt = null;
$conn = null; // Close the PDO connection
?>