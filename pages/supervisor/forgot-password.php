<?php
session_start();
include '../../includes/connection.php';

$error = '';
$success = '';
$redirect_url = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if user exists and is a supervisor
        $sql = "SELECT id, email, first_name, last_name FROM users WHERE email = ? AND role = 'supervisor' AND status = 'active' LIMIT 1";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            $error = 'Database error. Please try again later.';
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Generate 6-digit OTP
                $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                // Delete any existing reset tokens for this user
                $delete_sql = "DELETE FROM password_resets WHERE user_id = ? AND used = 0";
                $delete_stmt = $conn->prepare($delete_sql);
                if ($delete_stmt) {
                    $delete_stmt->bind_param("i", $user['id']);
                    $delete_stmt->execute();
                    $delete_stmt->close();
                }
                
                // Insert new reset token
                $insert_sql = "INSERT INTO password_resets (user_id, email, otp, token, expires_at) VALUES (?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                
                if ($insert_stmt === false) {
                    $error = 'Database error. Please try again later.';
                } else {
                    $insert_stmt->bind_param("issss", $user['id'], $email, $otp, $token, $expires_at);
                    
                    if ($insert_stmt->execute()) {
                        // Send OTP email
                        $user_name = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
                        $user_name = trim($user_name) ?: 'User';
                        
                        // Try to use PHPMailer if available
                        $email_sent = false;
                        $email_error = '';
                        
                        if (file_exists('../../includes/phpmailer_config.php')) {
                            require_once '../../includes/phpmailer_config.php';
                            
                            // Check if PHPMailer is available and function exists
                            if (function_exists('sendOTPEmail')) {
                                // Send OTP email to the email address provided in the form
                                error_log("Supervisor Forgot Password: Attempting to send OTP to: " . $email);
                                $email_sent = sendOTPEmail($email, $user_name, $otp);
                                
                                if (!$email_sent) {
                                    $email_error = "Failed to send email via PHPMailer. Check error logs.";
                                    error_log("Supervisor Forgot Password: Email sending failed for: " . $email);
                                }
                            } else {
                                error_log("Supervisor Forgot Password: sendOTPEmail function not found");
                            }
                        } else {
                            error_log("Supervisor Forgot Password: phpmailer_config.php not found");
                        }
                        
                        // Fallback to PHP mail() if PHPMailer fails or is not available
                        if (!$email_sent) {
                            error_log("Supervisor Forgot Password: Attempting fallback mail() function for: " . $email);
                            $subject = 'Password Reset OTP - SAC Cyberian Repository';
                            $message = "Hello " . $user_name . ",\n\n";
                            $message .= "You have requested to reset your password. Your OTP is: " . $otp . "\n\n";
                            $message .= "This OTP will expire in 15 minutes.\n\n";
                            $message .= "If you did not request this, please ignore this email.\n\n";
                            $message .= "SAC Cyberian Repository";
                            $headers = "From: neteronet@gmail.com\r\n";
                            $headers .= "Reply-To: neteronet@gmail.com\r\n";
                            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                            
                            $email_sent = @mail($email, $subject, $message, $headers);
                            
                            if ($email_sent) {
                                error_log("Supervisor Forgot Password: Fallback mail() succeeded for: " . $email);
                            } else {
                                error_log("Supervisor Forgot Password: Fallback mail() also failed for: " . $email);
                            }
                        }
                        
                        // Store session data and redirect to verify OTP page
                        $_SESSION['reset_token'] = $token;
                        $_SESSION['reset_email'] = $email;
                        $_SESSION['otp_code'] = $otp; // Store OTP for confirmation
                        
                        if ($email_sent) {
                            // OTP sent successfully - redirect to confirmation page first
                            $_SESSION['email_sent_success'] = true;
                            $redirect_url = "otp-sent-confirmation.php?email=" . urlencode($email);
                        } else {
                            // Email failed - show OTP on screen for testing
                            $_SESSION['otp_display'] = $otp;
                            $_SESSION['email_sent'] = false;
                            $_SESSION['email_sent_success'] = false;
                            $redirect_url = "verify-otp.php?sent=0&email=" . urlencode($email);
                        }
                    } else {
                        $error = 'Failed to create reset token. Please try again.';
                    }
                    
                    $insert_stmt->close();
                }
            } else {
                // Don't reveal if email exists or not (security best practice)
                $success = 'If the email exists in our system, a password reset OTP has been sent.';
            }
            
            $stmt->close();
        }
    }
}

// Redirect if needed (before any HTML output)
if (!empty($redirect_url)) {
    header("Location: " . $redirect_url);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SAC Cyberian Repository</title>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-sac-blue mb-2">Forgot Password</h2>
            <p class="text-gray-600">Enter your email to receive a password reset OTP</p>
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
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <input type="email" id="email" name="email" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200"
                    placeholder="Enter your registered email"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <button type="submit"
                class="w-full bg-sac-blue text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200 shadow-md hover:shadow-lg">
                Send OTP
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="login.php" class="text-sm text-sac-blue hover:text-sac-gold transition">
                &larr; Back to Login
            </a>
        </div>
    </div>
</main>

<?php
if (isset($conn)) $conn->close();
?>
</body>
</html>

