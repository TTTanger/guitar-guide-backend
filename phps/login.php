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
    'domain' => '.guitar-guide.org', // 适配主域和所有子域
    'secure' => true, // 生产环境建议 true
    'httponly' => true,
    'samesite' => 'None'
]);
session_start(); // Start the session to access session variables
require_once "decrypt.php"; // Include password decryption function
require_once "mysql.php";  // Include database connection

// Handle login only if the request method is POST (form submission)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]); // Get and trim username from POST
    $password = trim($_POST["password"]); // Get and trim password from POST
    $time = $_POST["time"];
    $password = decrypt($password, $time); // Decrypt the password (see decrypt.php for algorithm)

    $response = [];

    // Prepare SQL to fetch user by username (PDO version)
    $sql = "SELECT id, user_name, user_password FROM accounts WHERE user_name = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    if ($stmt->execute()) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            if ($password == $row['user_password']) {
                // Set session variables on successful login
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $row['id'];
                $_SESSION["username"] = $username;
                $response["success"] = true;
                $response["redirect"] = "../index.html"; // Redirect to homepage after login
            }
        }
    }
    // If login failed, set error message
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