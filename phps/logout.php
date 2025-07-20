<?php
session_start(); // Must start session before destroying it

try {
    // Check if session is active
    if (isset($_SESSION) && session_status() === PHP_SESSION_ACTIVE) {
        // Clear all session variables
        $_SESSION = array();

        // If session uses cookies, clear the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-3600, '/'); // Expire the session cookie
        }

        // Destroy the session
        session_destroy();

        // Prepare a successful logout response
        $response = array(
            "success" => true,
            "message" => "Logged out successfully!",
            "redirect" => "../htmls/login.html" // Redirect to login page after logout
        );
    } else {
        // No active session found
        throw new Exception("No active session found");
    }
} catch (Exception $e) {
    // Handle errors during logout
    $response = array(
        "success" => false,
        "error" => "Logout failed: " . $e->getMessage()
    );
}

header('Content-Type: application/json'); // Set response type to JSON
echo json_encode($response); // Output the response as JSON
exit;
?>