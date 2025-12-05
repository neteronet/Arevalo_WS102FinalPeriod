<?php
/**
 * OTP Sent Confirmation Page (Supervisor)
 * Shows confirmation that OTP was sent to email
 */
session_start();

$email = isset($_GET['email']) ? $_GET['email'] : (isset($_SESSION['reset_email']) ? $_SESSION['reset_email'] : '');
$otp = isset($_SESSION['otp_code']) ? $_SESSION['otp_code'] : '';

// If no email or token, redirect to forgot password
if (empty($email) || empty($_SESSION['reset_token'])) {
    header("Location: forgot-password.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Sent - SAC Cyberian Repository</title>
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
        @keyframes checkmark {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        .checkmark-animation {
            animation: checkmark 0.6s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

<main class="flex-grow flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 border border-gray-200">
        <div class="text-center mb-8">
            <div class="mb-4 flex justify-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center checkmark-animation">
                    <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
            <h2 class="text-3xl font-bold text-sac-blue mb-2">OTP Sent Successfully!</h2>
            <p class="text-gray-600">Check your email inbox for the OTP code</p>
        </div>

        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-800">Email Sent To:</p>
                    <p class="text-base text-sac-blue font-medium mt-1"><?php echo htmlspecialchars($email); ?></p>
                </div>
            </div>
        </div>

        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-bold text-green-800 text-sm">Email Delivered</p>
                    <p class="text-xs text-gray-600 mt-1">The OTP code has been sent to your Gmail account. Please check your inbox (and spam folder if needed).</p>
                </div>
            </div>
        </div>

        <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-bold text-yellow-800 text-sm">Important</p>
                    <ul class="text-xs text-gray-700 mt-1 space-y-1 list-disc list-inside">
                        <li>The OTP code expires in 15 minutes</li>
                        <li>Check your spam/junk folder if you don't see it</li>
                        <li>Enter the 6-digit code on the next page</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <a href="verify-otp.php?sent=1" 
               class="block w-full bg-sac-blue text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200 shadow-md hover:shadow-lg text-center">
                Enter OTP Code
            </a>
            
            <a href="forgot-password.php" 
               class="block w-full text-center text-sm text-sac-blue hover:text-sac-gold transition">
                Didn't receive email? Request New OTP
            </a>
        </div>

        <div class="mt-6 text-center text-xs text-gray-500">
            <p>Redirecting to OTP verification page in <span id="countdown">5</span> seconds...</p>
        </div>
    </div>
</main>

<script>
    // Auto-redirect countdown
    let countdown = 5;
    const countdownElement = document.getElementById('countdown');
    
    const timer = setInterval(function() {
        countdown--;
        if (countdownElement) {
            countdownElement.textContent = countdown;
        }
        
        if (countdown <= 0) {
            clearInterval(timer);
            window.location.href = 'verify-otp.php?sent=1';
        }
    }, 1000);

    // Show success message
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'OTP Sent!',
            text: 'Check your email inbox for the OTP code',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    });
</script>

</body>
</html>

