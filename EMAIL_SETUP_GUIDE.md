# Email Setup Guide - Ensure OTP Emails Show in Gmail

## Quick Setup Steps

### 1. Configure Gmail App Password

1. **Enable 2-Step Verification** on your Gmail account (`neteronet@gmail.com`)
   - Go to: https://myaccount.google.com/security
   - Enable 2-Step Verification if not already enabled

2. **Generate App Password**
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" as the app
   - Select "Other (Custom name)" as device
   - Enter "SAC Repository" as the name
   - Click "Generate"
   - Copy the 16-character password (e.g., `abcd efgh ijkl mnop`)

### 2. Update PHPMailer Configuration

1. Open: `git/includes/phpmailer_config.php`
2. Find line 52: `$mail->Password = 'your_app_password_here';`
3. Replace `'your_app_password_here'` with your Gmail App Password
   ```php
   $mail->Password = 'abcdefghijklmnop'; // Your 16-character app password (no spaces)
   ```

### 3. Test Email Sending

1. Open: `http://localhost/Arevalo_WS102FInalPeriod/git/test-email.php`
2. Enter your email address
3. Click "Send Test Email"
4. Check your Gmail inbox (and spam folder)

## How It Works

### Email Flow:
1. User enters email in **Forgot Password** form
2. System generates 6-digit OTP
3. Email is sent **TO** the email address entered in the form
4. Email is sent **FROM** `neteronet@gmail.com`
5. User receives OTP in their Gmail inbox

### Important Notes:

- ✅ **Recipient**: The email address entered in the forgot password form
- ✅ **Sender**: `neteronet@gmail.com` (SAC Cyberian Repository)
- ✅ **Subject**: "Password Reset OTP - SAC Cyberian Repository"
- ✅ **Content**: HTML email with OTP code displayed prominently

## Troubleshooting

### Email Not Received?

1. **Check Configuration**
   - Verify Gmail App Password is set correctly in `phpmailer_config.php`
   - Make sure there are no extra spaces in the password

2. **Check PHP Error Logs**
   - Location: `C:\xampp\php\logs\php_error_log` (or your PHP error log location)
   - Look for messages starting with "sendOTPEmail:" or "PHPMailer:"

3. **Check Gmail**
   - Check Spam/Junk folder
   - Check All Mail folder
   - Verify email address is correct

4. **Test Email Page**
   - Use `test-email.php` to test email sending
   - This will show detailed error messages

5. **Enable Debug Mode** (Temporary)
   - In `phpmailer_config.php`, change line 42:
     ```php
     $mail->SMTPDebug = 2; // Enable verbose debug output
     ```
   - This will show detailed SMTP communication in error logs
   - **Remember to set back to 0 for production**

### Common Issues

**Issue**: "Gmail App Password not configured"
- **Solution**: Replace `'your_app_password_here'` with actual app password

**Issue**: "Failed to send email"
- **Solution**: Check PHP error logs for specific error message
- Verify Gmail account has 2-Step Verification enabled
- Verify App Password is correct

**Issue**: Email goes to spam
- **Solution**: This is normal for automated emails. Check spam folder.
- Consider adding SPF/DKIM records (advanced)

## Files Involved

- `git/includes/phpmailer_config.php` - Email configuration
- `git/pages/student/forgot-password.php` - Student forgot password page
- `git/pages/supervisor/forgot-password.php` - Supervisor forgot password page
- `git/test-email.php` - Test email sending page

## Security Notes

- Never commit Gmail App Password to version control
- Use environment variables for production (recommended)
- OTP expires in 15 minutes
- OTP can only be used once

