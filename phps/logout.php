<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '.guitar-guide.org',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None'
]);
session_start();
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