<?php
session_start();
include '../../includes/connection.php';

$error = '';
$email = $_SESSION['reset_email'] ?? '';
$token = $_SESSION['reset_token'] ?? '';
$redirect_url = null;

// Check if user came from redirect with sent parameter
$otp_sent = isset($_GET['sent']) && $_GET['sent'] == '1';

// If no token or email in session, redirect to forgot password
if (empty($token) || empty($email)) {
    $redirect_url = "forgot-password.php";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $otp_input = trim($_POST['otp'] ?? '');
    
    if (empty($otp_input) || strlen($otp_input) !== 6 || !ctype_digit($otp_input)) {
        $error = 'Please enter a valid 6-digit OTP.';
    } else {
        // Verify OTP against database
        $verify_sql = "SELECT id, user_id, email, otp, expires_at, used FROM password_resets WHERE token = ? AND otp = ? AND email = ? AND used = 0 LIMIT 1";
        $verify_stmt = $conn->prepare($verify_sql);
        
        if ($verify_stmt === false) {
            $error = 'Database error. Please try again.';
        } else {
            $verify_stmt->bind_param("sss", $token, $otp_input, $email);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            
            if ($verify_result && $verify_result->num_rows === 1) {
                $otp_data = $verify_result->fetch_assoc();
                
                // Check expiration
                if (strtotime($otp_data['expires_at']) <= time()) {
                    $error = 'Your OTP has expired. Please request a new one.';
                } else {
                    // OTP is valid: mark as verified in session
                    $_SESSION['otp_verified'] = true;
                    $_SESSION['reset_user_id'] = $otp_data['user_id'];
                    
                    // Redirect to reset password page
                    $redirect_url = "reset-password.php?token=" . urlencode($token);
                }
            } else {
                $error = 'Invalid OTP. Please check the code in your email and try again.';
            }
            
            $verify_stmt->close();
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
    <title>Verify OTP - SAC Cyberian Repository</title>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-sac-blue mb-2">Verify OTP</h2>
            <p class="text-gray-600">Enter the 6-digit code sent to your email</p>
        </div>

        <?php if (!empty($email)): ?>
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-center">
                <p class="text-sm text-gray-700">
                    <span class="font-semibold">Email:</span> <?php echo htmlspecialchars($email); ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if ($otp_sent): ?>
            <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg shadow-md" role="alert">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-bold text-base">OTP Sent Successfully</p>
                        <p class="text-sm mt-1">OTP has been sent to your email. Please check your inbox.</p>
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

        <?php if (isset($_SESSION['otp_display']) && !empty($_SESSION['otp_display'])): ?>
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
                        <p class="text-2xl font-bold text-sac-blue mt-2 text-center"><?php echo htmlspecialchars($_SESSION['otp_display']); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="otp" class="block text-sm font-medium text-gray-700 mb-2 text-center">
                    <strong>Enter OTP Code:</strong>
                </label>
                <input type="text" 
                       id="otp" 
                       name="otp" 
                       placeholder="000000" 
                       maxlength="6" 
                       pattern="[0-9]{6}"
                       required 
                       autocomplete="off"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-gold focus:border-transparent transition duration-200 text-center text-2xl tracking-widest font-bold"
                       value="<?php echo isset($_POST['otp']) ? htmlspecialchars($_POST['otp']) : ''; ?>">
            </div>

            <button type="submit" name="verify_otp" class="w-full bg-sac-blue text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200 shadow-md hover:shadow-lg">
                Verify OTP
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="forgot-password.php" class="text-sm text-sac-blue hover:text-sac-gold transition">
                Request New OTP
            </a>
        </div>
    </div>
</main>

<script>
    // Keep OTP numeric-only
    document.getElementById('otp').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Focus on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('otp').focus();
    });
</script>

<?php
if (isset($conn)) $conn->close();
?>
</body>
</html>

