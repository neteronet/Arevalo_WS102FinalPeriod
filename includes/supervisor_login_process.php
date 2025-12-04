<?php
// supervisor_login_process.php - Backend logic for supervisor login processing

// Start the session to manage user login state
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection (uses mysqli $conn)
include 'connection.php';

// --- Login Logic ---
$login_error = ''; // Variable to hold login error messages
$username_or_email = ''; // To retain input value

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Sanitize and retrieve user input
    $username_or_email = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password          = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username_or_email) || empty($password)) {
        $login_error = "Please enter both Supervisor ID/Email and password.";
    } else {
        // Supervisors can log in using supervisor_id OR email
        // Only allow active supervisor accounts
        $sql = "SELECT id, username, supervisor_id, email, password, role, status 
                FROM users 
                WHERE (supervisor_id = ? OR email = ?) 
                  AND role = 'supervisor' 
                  AND status = 'active'
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            $login_error = "Database error while processing your login. Please try again later.";
        } else {
            $stmt->bind_param("ss", $username_or_email, $username_or_email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id']        = $user['id'];
                    $_SESSION['username']       = $user['username'];
                    $_SESSION['supervisor_id']  = $user['supervisor_id'];
                    $_SESSION['email']          = $user['email'];
                    $_SESSION['role']           = $user['role'];
                    $_SESSION['loggedin']       = true;

                    // Redirect to supervisor dashboard (relative to pages/supervisor/login.php)
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $login_error = "Incorrect password. Please try again.";
                }
            } else {
                $login_error = "Account not found or inactive. Please check your Supervisor ID or email and try again. If you don't have an account, please sign up.";
            }

            $stmt->close();
        }
    }
}
?>


