<?php
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

    // Prepare SQL to fetch user by username
    $sql = "SELECT id, user_name, user_password FROM accounts WHERE user_name = ?";
    
    if($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username); // Bind username as string
        
        if($stmt->execute()) {
            $stmt->store_result();
            
            if($stmt->num_rows == 1) {
                $stmt->bind_result($id, $db_username, $db_password);
                if($stmt->fetch()) {
                    if($password == $db_password) { 
                        // Set session variables on successful login
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $username;
                        
                        $response["success"] = true;
                        $response["redirect"] = "../htmls/index.html"; // Redirect to homepage after login
                    }
                }
            }
        }
        
        // If login failed, set error message
        if(!isset($response["success"])) {
            $response["success"] = false;
            $response["error"] = "Invalid username or password.";
        }
        
        $stmt->close();
    }
    
    header('Content-Type: application/json'); // Set response type to JSON
    echo json_encode($response); // Output the response as JSON
    $conn->close(); // Close the database connection
    exit;
}
?>