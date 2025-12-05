<?php
session_start();
include '../../includes/connection.php';
include 'header.php';

$error = '';
$success = '';
$token = isset($_GET['token']) ? $_GET['token'] : (isset($_SESSION['reset_token']) ? $_SESSION['reset_token'] : '');

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
    $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    if (empty($otp) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif (strlen($new_password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Verify OTP
        $verify_sql = "SELECT id FROM password_resets WHERE token = ? AND otp = ? AND user_id = ? AND used = 0 AND expires_at > NOW() LIMIT 1";
        $verify_stmt = $conn->prepare($verify_sql);
        
        if ($verify_stmt === false) {
            $error = 'Database error. Please try again.';
        } else {
            $verify_stmt->bind_param("ssi", $token, $otp, $user_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            
            if ($verify_result && $verify_result->num_rows === 1) {
                // OTP is valid, update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_sql = "UPDATE users SET password = ? WHERE id = ? AND role = 'student'";
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
                        
                        unset($_SESSION['reset_token']);
                        unset($_SESSION['reset_email']);
                        
                        $success = 'Password reset successfully! You can now login with your new password.';
                    } else {
                        $error = 'Failed to update password. Please try again.';
                    }
                    
                    $update_stmt->close();
                }
            } else {
                $error = 'Invalid OTP. Please check and try again.';
            }
            
            $verify_stmt->close();
        }
    }
}
?>

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
            <form action="" method="POST" class="space-y-6">
                <div>
                    <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">OTP (6 digits)</label>
                    <input type="text" id="otp" name="otp" required maxlength="6" pattern="[0-9]{6}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200 text-center text-2xl tracking-widest"
                        placeholder="000000"
                        value="<?php echo isset($_POST['otp']) ? htmlspecialchars($_POST['otp']) : ''; ?>">
                    <p class="text-xs text-gray-500 mt-1">Enter the 6-digit code sent to <?php echo htmlspecialchars($email); ?></p>
                </div>

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
                <a href="forgot-password.php" class="text-sm text-sac-blue hover:text-sac-gold transition">
                    Request New OTP
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

