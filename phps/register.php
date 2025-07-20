<?php
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
    
    // Check if the username already exists in the database
    $sql = "SELECT user_name FROM accounts WHERE user_name = ?";
    
    if($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username); // Bind username as string
        
        if($stmt->execute()) {
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                // Username already exists
                $response["success"] = false;
                $response["error"] = "User existed!";
            }
            else {
                $stmt->close();
                $date = date("Y-m-d"); // Current date for account creation
                $avatar = "../images/default_avatar.jpeg"; // Default avatar path
                
                // Insert the new user into the database
                $sql = "INSERT INTO accounts (user_name, user_password, user_avatar, created_at) VALUES (?, ?, ?, ?)";
                if($stmt = $conn->prepare($sql)) {
                    
                    $stmt->bind_param("ssss", $username, $password, $avatar, $date);
                    if($stmt->execute()) {
                        $stmt->store_result();
                        $response["success"] = true;
                        $response["message"] = "User created successfully!";
                        $response["redirect"] = "../htmls/login.html"; // Redirect to login page after registration
                    }
                }
            }
        }
        
        // If registration failed, set error message
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