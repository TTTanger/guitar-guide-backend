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
$sql = "UPDATE accounts SET best_score = GREATEST(COALESCE(best_score, 0), ?) WHERE id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $score, $id); // Bind score and user ID as integers
    $result = $stmt->execute();
    if ($result) {
        // If update is successful, return success message and new score
        echo json_encode([
            'success' => true,
            'message' => 'Score updated successfully',
            'score' => $score
        ]);
    } else {
        // If update fails, return error message
        echo json_encode(['success' => false, 'error' => 'Failed to update score']);
    }
    $stmt->close();
}
$conn->close(); // Close the database connection
?>