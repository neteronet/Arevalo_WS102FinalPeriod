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

// Try to load PHPMailer via Composer autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $phpmailer_available = true;
} elseif (file_exists(__DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
    // Fallback: direct file includes (if not using Composer)
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    $phpmailer_available = true;
}

/**
 * Configure and return PHPMailer instance
 */
function getPHPMailer() {
    global $phpmailer_available;
    
    if (!$phpmailer_available) {
        error_log("PHPMailer not available - autoloader or files not found");
        return false;
    }
    
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        // Enable verbose debug output (set to 0 for production, 2 for debugging)
        // Set to 2 temporarily to debug email sending issues
        $mail->SMTPDebug = 0; // 0 = off, 2 = client and server messages
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug: $str");
        };
        
        // Additional SMTP options for Gmail
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'neteronet@gmail.com'; // Your Gmail address
        $mail->Password   = 'your_app_password_here'; // Gmail App Password (not regular password)
        // IMPORTANT: Replace 'your_app_password_here' with your actual Gmail App Password
        // Get it from: https://myaccount.google.com/apppasswords
        // Remove spaces when copying (e.g., "abcd efgh ijkl mnop" → "abcdefghijklmnop")
        
        // Check if password is still default - but don't fail, allow fallback
        if ($mail->Password === 'your_app_password_here' || empty($mail->Password)) {
            error_log("PHPMailer: Gmail App Password not configured! Email will use fallback method.");
            // Return mail object anyway to allow fallback email sending
        }
        
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        
        // Sender info
        $mail->setFrom('neteronet@gmail.com', 'SAC Cyberian Repository');
        
        return $mail;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log("PHPMailer configuration error: " . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log("PHPMailer general error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send OTP email to the specified email address
 * 
 * @param string $to_email The recipient's email address (from forgot password form)
 * @param string $to_name The recipient's name
 * @param string $otp The 6-digit OTP code
 * @return bool True if email sent successfully, false otherwise
 */
function sendOTPEmail($to_email, $to_name, $otp) {
    // Validate email address
    if (empty($to_email) || !filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
        error_log("sendOTPEmail: Invalid email address provided: " . $to_email);
        return false;
    }
    
    // Log attempt to send email
    error_log("sendOTPEmail: Attempting to send OTP to: " . $to_email . " (Name: " . $to_name . ")");
    
    $mail = getPHPMailer();
    
    if (!$mail) {
        error_log("sendOTPEmail: Failed to get PHPMailer instance. Check configuration.");
        return false;
    }
    
    try {
        // Clear any previous recipients
        $mail->clearAddresses();
        $mail->clearReplyTos();
        
        // Add recipient - this is the email from the forgot password form
        $mail->addAddress($to_email, $to_name);
        
        // Add reply-to address
        $mail->addReplyTo('neteronet@gmail.com', 'SAC Cyberian Repository');
        
        // Log recipient address for debugging
        error_log("sendOTPEmail: Sending OTP email to recipient: " . $to_email);
        error_log("sendOTPEmail: From: neteronet@gmail.com");
        error_log("sendOTPEmail: OTP Code: " . $otp);
        
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
        
        // Send email
        $result = $mail->send();
        
        if ($result) {
            error_log("sendOTPEmail: SUCCESS - OTP email sent successfully to: " . $to_email);
            return true;
        } else {
            error_log("sendOTPEmail: FAILED - Mail->send() returned false for: " . $to_email);
            error_log("sendOTPEmail: Error Info: " . $mail->ErrorInfo);
            return false;
        }
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        $error_msg = "PHPMailer Exception sending to " . $to_email . ": " . $mail->ErrorInfo;
        error_log($error_msg);
        error_log("PHPMailer Exception Details: " . $e->getMessage());
        return false;
    } catch (Exception $e) {
        $error_msg = "General error sending email to " . $to_email . ": " . $e->getMessage();
        error_log($error_msg);
        return false;
    }
}
?>

