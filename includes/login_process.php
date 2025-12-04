<?php
// login_process.php - Backend logic for student login processing

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
        $login_error = "Please enter both Student ID/Email and password.";
    } else {
        // 2. Prepare SQL statement
        // Students can log in using student_id OR email
        // Only allow active student accounts
        $sql = "SELECT id, username, student_id, email, password, role, status 
                FROM users 
                WHERE (student_id = ? OR email = ?) 
                  AND role = 'student' 
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

                // 3. Verify the submitted password against the stored hash
                if (password_verify($password, $user['password'])) {
                    // Success! Password is correct.

                    // 4. Set session variables
                    $_SESSION['user_id']      = $user['id'];
                    $_SESSION['username']     = $user['username'];
                    $_SESSION['student_id']   = $user['student_id'];
                    $_SESSION['email']        = $user['email'];
                    $_SESSION['role']         = $user['role'];
                    $_SESSION['loggedin']     = true;

                    // 5. Redirect to student dashboard (relative to student/login.php)
                    header("Location: dashboard.php");
                    exit;
                } else {
                    // Password verification failed
                    $login_error = "Incorrect password. Please try again.";
                }
            } else {
                // User not found in database
                $login_error = "Account not found or inactive. Please check your Student ID or email and try again. If you don't have an account, please sign up.";
            }

            $stmt->close();
        }
    }
}
?>
