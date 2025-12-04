<?php
// login_process.php - Backend logic for login processing

// Start the session to manage user login state
session_start();

// Include database connection
include 'connection.php'; // Assumes $pdo is the PDO connection object

// --- Login Logic ---
$login_error = ''; // Variable to hold login error messages
$username_or_email = ''; // To retain input value

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize and retrieve user input
    $username_or_email = trim($_POST['username']);
    $password          = $_POST['password'];

    if (empty($username_or_email) || empty($password)) {
        $login_error = "Please enter both username/email and password.";
    } else {
        // 2. Prepare SQL statement (searches by either username OR email)
        $sql = "SELECT id, username, password_hash FROM users WHERE username = :login_input OR email = :login_input";

        // Using prepared statements prevents SQL Injection
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':login_input', $username_or_email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Check if user exists and verify password
        if ($user) {
            // Verify the submitted password against the stored hash
            if (password_verify($password, $user['password_hash'])) {
                // Success! Password is correct.

                // 4. Set session variables
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['loggedin']  = true;

                // 5. Redirect to a protected page (e.g., dashboard)
                header("Location: dashboard.php"); // Create a dashboard.php file
                exit;

            } else {
                // Password verification failed
                $login_error = "Incorrect password. Please try again or use the forgot password option to reset your password.";
            }
        } else {
            // User not found in database
            $login_error = "Account not found. Please check your Student ID/Supervisor ID or email and try again. If you don't have an account, please sign up.";
        }
    }
}
?>
