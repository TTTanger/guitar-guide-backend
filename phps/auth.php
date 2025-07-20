<?php
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