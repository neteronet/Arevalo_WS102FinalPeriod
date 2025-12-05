<?php
/**
 * Test Email Configuration
 * 
 * This file tests if PHPMailer and email configuration are working correctly
 * 
 * Usage: http://localhost/Arevalo_WS102FInalPeriod/git/test-email.php
 * 
 * ⚠️ Make sure to configure includes/phpmailer_config.php first!
 */

session_start();

// Check if PHPMailer is installed
$phpmailer_installed = false;
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    $phpmailer_installed = true;
} elseif (file_exists(__DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
    require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
    require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
    $phpmailer_installed = true;
}

// Load email config
$config_ok = false;
$config_details = [];
if (file_exists(__DIR__ . '/includes/phpmailer_config.php')) {
    require_once __DIR__ . '/includes/phpmailer_config.php';
    
    // Check configuration
    if (function_exists('getPHPMailer')) {
        $test_mail = getPHPMailer();
        if ($test_mail !== false) {
            $config_ok = true;
            $config_details = [
                'host' => $test_mail->Host,
                'port' => $test_mail->Port,
                'username' => $test_mail->Username,
                'from_email' => 'neteronet@gmail.com',
                'from_name' => 'SAC Cyberian Repository',
                'password_configured' => ($test_mail->Password !== 'your_app_password_here' && !empty($test_mail->Password))
            ];
        }
    }
}

// Handle test email
$test_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email']) && $config_ok && $phpmailer_installed) {
    $test_email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    
    if ($test_email) {
        $test_otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $test_name = 'Test User';
        
        if (function_exists('sendOTPEmail')) {
            $email_sent = sendOTPEmail($test_email, $test_name, $test_otp);
            
            if ($email_sent) {
                $test_result = [
                    'success' => true,
                    'message' => 'Email sent successfully! Check your inbox (and spam folder).',
                    'otp' => $test_otp,
                    'recipient' => $test_email
                ];
            } else {
                $test_result = [
                    'success' => false,
                    'message' => 'Failed to send email. Check PHP error logs for details.',
                    'otp' => $test_otp,
                    'recipient' => $test_email
                ];
            }
        } else {
            $test_result = [
                'success' => false,
                'message' => 'sendOTPEmail function not found.'
            ];
        }
    } else {
        $test_result = [
            'success' => false,
            'message' => 'Invalid email address.'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email Configuration - SAC Cyberian Repository</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-sac-blue mb-6">📧 Test Email Configuration</h1>
            
            <!-- PHPMailer Installation Check -->
            <div class="mb-6">
                <?php if ($phpmailer_installed): ?>
                    <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="font-bold">✅ PHPMailer is installed</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-red-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="font-bold">❌ PHPMailer not found</p>
                                <p class="text-sm mt-1">Please run: <code class="bg-gray-200 px-2 py-1 rounded">cd git && composer install</code></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Configuration Check -->
            <div class="mb-6">
                <?php if ($config_ok && !empty($config_details)): ?>
                    <?php if ($config_details['password_configured']): ?>
                        <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg mb-4">
                            <div class="flex items-start">
                                <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <p class="font-bold">✅ Email configuration found</p>
                                    <ul class="text-sm mt-2 space-y-1">
                                        <li><strong>SMTP Host:</strong> <?php echo htmlspecialchars($config_details['host']); ?></li>
                                        <li><strong>SMTP Port:</strong> <?php echo htmlspecialchars($config_details['port']); ?></li>
                                        <li><strong>From Email:</strong> <?php echo htmlspecialchars($config_details['from_email']); ?></li>
                                        <li><strong>From Name:</strong> <?php echo htmlspecialchars($config_details['from_name']); ?></li>
                                        <li><strong>Gmail App Password:</strong> ✅ Configured</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 rounded-lg">
                            <div class="flex items-start">
                                <svg class="h-5 w-5 text-yellow-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <p class="font-bold">⚠️ Email password not configured</p>
                                    <p class="text-sm mt-1">Please edit <code class="bg-gray-200 px-2 py-1 rounded">includes/phpmailer_config.php</code> and set your Gmail App Password.</p>
                                    <p class="text-sm mt-2">
                                        <strong>Steps:</strong>
                                    </p>
                                    <ol class="text-sm mt-1 ml-4 list-decimal">
                                        <li>Go to <a href="https://myaccount.google.com/apppasswords" target="_blank" class="text-blue-600 underline">Google App Passwords</a></li>
                                        <li>Generate a new App Password for "Mail"</li>
                                        <li>Copy the 16-character password</li>
                                        <li>Replace <code class="bg-gray-200 px-1 rounded">'your_app_password_here'</code> in phpmailer_config.php</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-red-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="font-bold">❌ Email configuration error</p>
                                <p class="text-sm mt-1">Check that <code class="bg-gray-200 px-2 py-1 rounded">includes/phpmailer_config.php</code> exists and is configured correctly.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Test Result -->
            <?php if ($test_result): ?>
                <div class="mb-6">
                    <?php if ($test_result['success']): ?>
                        <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg">
                            <div class="flex items-start">
                                <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <p class="font-bold">✅ <?php echo htmlspecialchars($test_result['message']); ?></p>
                                    <p class="text-sm mt-2">
                                        <strong>Sent to:</strong> <?php echo htmlspecialchars($test_result['recipient']); ?><br>
                                        <strong>Test OTP Code:</strong> <span class="font-mono text-lg font-bold"><?php echo htmlspecialchars($test_result['otp']); ?></span>
                                    </p>
                                    <p class="text-xs mt-2 text-gray-600">Check your inbox and spam folder. The email should arrive within a few seconds.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg">
                            <div class="flex items-start">
                                <svg class="h-5 w-5 text-red-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <p class="font-bold">❌ <?php echo htmlspecialchars($test_result['message']); ?></p>
                                    <?php if (isset($test_result['otp'])): ?>
                                        <p class="text-sm mt-2">
                                            <strong>Test OTP Code:</strong> <span class="font-mono text-lg font-bold"><?php echo htmlspecialchars($test_result['otp']); ?></span>
                                        </p>
                                    <?php endif; ?>
                                    <p class="text-xs mt-2 text-gray-600">Check PHP error logs for more details.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Test Form -->
            <?php if ($config_ok && $phpmailer_installed && $config_details['password_configured']): ?>
                <form method="POST" class="space-y-4">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-3">Send Test Email</h3>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Enter your email address to receive a test OTP:
                        </label>
                        <input type="email" 
                               name="email" 
                               placeholder="your-email@example.com" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sac-blue focus:border-transparent"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <button type="submit" 
                            name="test_email"
                            class="w-full bg-sac-blue text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200 shadow-md hover:shadow-lg">
                        📧 Send Test Email
                    </button>
                </form>
            <?php else: ?>
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h3 class="font-semibold text-blue-900 mb-2">Setup Instructions:</h3>
                    <ol class="list-decimal ml-5 space-y-2 text-sm text-blue-800">
                        <li>Open <code class="bg-blue-100 px-2 py-1 rounded">includes/phpmailer_config.php</code></li>
                        <li>Set your Gmail address (already set to: <code class="bg-blue-100 px-2 py-1 rounded">neteronet@gmail.com</code>)</li>
                        <li>Get Gmail App Password from <a href="https://myaccount.google.com/apppasswords" target="_blank" class="text-blue-600 underline font-semibold">Google App Passwords</a></li>
                        <li>Set the App Password in <code class="bg-blue-100 px-2 py-1 rounded">SMTP_PASSWORD</code> (replace <code class="bg-blue-100 px-2 py-1 rounded">'your_app_password_here'</code>)</li>
                        <li>Refresh this page and test again</li>
                    </ol>
                </div>
            <?php endif; ?>

            <hr class="my-6">

            <div class="text-center">
                <a href="pages/student/forgot-password.php" class="text-sac-blue hover:text-sac-gold transition">
                    ← Back to Forgot Password Page
                </a>
            </div>
        </div>
    </div>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sac-blue': '#0A3D62',
                        'sac-gold': '#FBC531',
                    }
                }
            }
        }
    </script>
</body>
</html>

