<?php
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
    'domain' => '.guitar-guide.org',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None'
]);
session_start(); // Start the session to access session variables
require_once "decrypt.php"; // Include password decryption function
require_once "mysql.php";  // Include database connection

// Handle registration only if the request method is POST (form submission)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]); // Get and trim username from POST
    $password = trim($_POST["password"]); // Get and trim password from POST
    $time = $_POST['time']; // Get the time from the POST request
    $password = decrypt($password, $time); // Decrypt the password (see decrypt.php for algorithm)
    
    // Check password length
    if (strlen($password) < 6) {
        $response["success"] = false;
        $response["error"] = "Password must be at least 6 characters long";
        echo json_encode($response);
        exit;
    }
    
    // Check if the username already exists in the database (PDO version)
    $sql = "SELECT user_name FROM accounts WHERE user_name = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    if($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            // Username already exists
            $response["success"] = false;
            $response["error"] = "User existed!";
        } else {
            $date = date("Y-m-d"); // Current date for account creation
            $avatar = "../images/default_avatar.jpeg"; // Default avatar path
            // Insert the new user into the database
            $sql = "INSERT INTO accounts (user_name, user_password, user_avatar, created_at) VALUES (:username, :password, :avatar, :created_at)";
            $stmt2 = $conn->prepare($sql);
            $stmt2->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt2->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt2->bindParam(':avatar', $avatar, PDO::PARAM_STR);
            $stmt2->bindParam(':created_at', $date, PDO::PARAM_STR);
            if($stmt2->execute()) {
                $response["success"] = true;
                $response["message"] = "User created successfully!";
                $response["redirect"] = "../login.html"; // Redirect to login page after registration
            }
            $stmt2 = null;
        }
    }
    // If registration failed, set error message
    if(!isset($response["success"])) {
        $response["success"] = false;
        $response["error"] = "Invalid username or password.";
    }
    $stmt = null;
    
    header('Content-Type: application/json'); // Set response type to JSON
    echo json_encode($response); // Output the response as JSON
    $conn = null; // Close the PDO connection
    exit;
}
?>