<?php
/**
 * PHPMailer Configuration
 * Configure PHPMailer to send emails via Gmail SMTP
 * 
 * Installation:
 * 1. Download PHPMailer from: https://github.com/PHPMailer/PHPMailer
 * 2. Extract to: git/vendor/phpmailer/phpmailer/
 * OR use Composer: composer require phpmailer/phpmailer
 */

// Check if PHPMailer is available
$phpmailer_available = false;

// Try to load PHPMailer
if (file_exists(__DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    $phpmailer_available = true;
}

/**
 * Configure and return PHPMailer instance
 */
function getPHPMailer() {
    global $phpmailer_available;
    
    if (!$phpmailer_available) {
        return false;
    }
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'neteronet@gmail.com'; // Your Gmail address
        $mail->Password   = 'your_app_password_here'; // Gmail App Password (not regular password)
        // IMPORTANT: Replace 'your_app_password_here' with your actual Gmail App Password
        // Get it from: https://myaccount.google.com/apppasswords
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        
        // Sender info
        $mail->setFrom('neteronet@gmail.com', 'SAC Cyberian Repository');
        
        return $mail;
    } catch (Exception $e) {
        error_log("PHPMailer configuration error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send OTP email
 */
function sendOTPEmail($to_email, $to_name, $otp) {
    $mail = getPHPMailer();
    
    if (!$mail) {
        return false;
    }
    
    try {
        // Recipient
        $mail->addAddress($to_email, $to_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP - SAC Cyberian Repository';
        $mail->Body    = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #0A3D62; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background-color: #f9f9f9; }
                    .otp-box { background-color: #FBC531; color: #0A3D62; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; margin: 20px 0; border-radius: 5px; }
                    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>SAC Cyberian Repository</h2>
                    </div>
                    <div class='content'>
                        <p>Hello " . htmlspecialchars($to_name) . ",</p>
                        <p>You have requested to reset your password. Please use the following OTP (One-Time Password) to proceed:</p>
                        <div class='otp-box'>" . htmlspecialchars($otp) . "</div>
                        <p>This OTP will expire in 15 minutes.</p>
                        <p>If you did not request this password reset, please ignore this email.</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " SAC Cyberian Repository. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        $mail->AltBody = "Hello " . $to_name . ",\n\nYou have requested to reset your password. Your OTP is: " . $otp . "\n\nThis OTP will expire in 15 minutes.\n\nIf you did not request this, please ignore this email.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}
?>

