<?php
/* 
 * Database Configuration 
 * Pattern: Singleton-like connection setup
 * Reasoning: Centralizes credentials. If DB changes, you only edit this file.
 */

$servername = "localhost";
$username = "root"; // Default XAMPP/WAMP user
$password = "";     // Default XAMPP/WAMP pass
$dbname = "sac_repository";

// Enable error reporting for development (Disable in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4"); // Security: Prevents specific SQL injection attacks via encoding
} catch (Exception $e) {
    // We catch the error to prevent sensitive credential exposure on the screen
    $db_error = "Database Connection Failed: " . $e->getMessage();
}
?>