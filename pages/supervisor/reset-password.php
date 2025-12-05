<?php
session_start();
include '../../includes/connection.php';

$error = '';
$success = '';
$token = isset($_GET['token']) ? $_GET['token'] : (isset($_SESSION['reset_token']) ? $_SESSION['reset_token'] : '');

// Check if OTP is verified
if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: verify-otp.php");
    exit;
}

if (empty($token)) {
    header("Location: forgot-password.php");
    exit;
}

// Verify token and get user info
$user_id = null;
$email = null;

$sql = "SELECT user_id, email, expires_at, used FROM password_resets WHERE token = ? AND used = 0 LIMIT 1";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    $error = 'Database error. Please try again.';
} else {
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows === 1) {
        $reset = $result->fetch_assoc();
        
        // Check if token is expired
        if (strtotime($reset['expires_at']) < time()) {
            $error = 'This reset link has expired. Please request a new one.';
        } else {
            $user_id = $reset['user_id'];
            $email = $reset['email'];
        }
    } else {
        $error = 'Invalid or expired reset token.';
    }
    
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($user_id)) {
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif (strlen($new_password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // OTP already verified, update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = ? WHERE id = ? AND role = 'supervisor'";
        $update_stmt = $conn->prepare($update_sql);
        
        if ($update_stmt === false) {
            $error = 'Database error. Please try again.';
        } else {
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                // Mark token as used
                $mark_used_sql = "UPDATE password_resets SET used = 1 WHERE token = ?";
                $mark_stmt = $conn->prepare($mark_used_sql);
                if ($mark_stmt) {
                    $mark_stmt->bind_param("s", $token);
                    $mark_stmt->execute();
                    $mark_stmt->close();
                }
                
                // Clear session variables
                unset($_SESSION['reset_token']);
                unset($_SESSION['reset_email']);
                unset($_SESSION['otp_verified']);
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['otp_display']);
                
                $success = 'Password reset successfully! You can now login with your new password.';
            } else {
                $error = 'Failed to update password. Please try again.';
            }
            
            $update_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SAC Cyberian Repository</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../../css/styles.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sac-blue': '#0A3D62',
                        'sac-gold': '#FBC531',
                        'cyber-dark': '#1f1f2e',
                    },
                    fontFamily: {
                        'sans': ['Source Sans Pro', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

<main class="flex-grow flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 border border-gray-200">
        <div class="text-center mb-8">
            <div class="mb-4 flex justify-center">
                <svg class="w-16 h-16 text-sac-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-sac-blue mb-2">Reset Password</h2>
            <p class="text-gray-600">Enter the OTP sent to your email and set a new password</p>
        </div>

        <?php if ($email_failed && !empty($otp_display)): ?>
            <div class="mb-4 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 rounded-lg shadow-md" role="alert">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-bold text-base">Email Not Sent</p>
                        <p class="text-sm mt-1">Email sending failed. For testing purposes, your OTP is:</p>
                        <p class="text-2xl font-bold text-sac-blue mt-2 text-center"><?php echo htmlspecialchars($otp_display); ?></p>
                        <p class="text-xs mt-2 text-gray-600">Please configure Gmail App Password in phpmailer_config.php to enable email sending.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg shadow-md" role="alert">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-bold text-base">Error</p>
                        <p class="text-sm mt-1"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg shadow-md" role="alert">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-bold text-base">Success</p>
                        <p class="text-sm mt-1"><?php echo htmlspecialchars($success); ?></p>
                    </div>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="login.php" class="inline-block px-6 py-2 bg-sac-blue text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                    Go to Login
                </a>
            </div>
        <?php elseif (!empty($user_id)): ?>
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-center">
                <p class="text-sm text-gray-700">
                    <span class="font-semibold">Email:</span> <?php echo htmlspecialchars($email); ?>
                </p>
                <p class="text-xs text-gray-600 mt-1">OTP verified successfully. Please set your new password.</p>
            </div>

            <form action="" method="POST" class="space-y-6">
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                        placeholder="Enter new password (min. 8 characters)">
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                        placeholder="Confirm new password">
                </div>

                <button type="submit"
                    class="w-full bg-sac-blue text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200 shadow-md hover:shadow-lg">
                    Reset Password
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="verify-otp.php" class="text-sm text-sac-blue hover:text-sac-gold transition">
                    Back to Verify OTP
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
if (isset($conn)) $conn->close();
?>
</body>
</html>

