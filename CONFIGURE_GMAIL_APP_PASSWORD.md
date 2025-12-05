# How to Configure Gmail App Password for OTP Emails

## Quick Setup (5 minutes)

### Step 1: Enable 2-Step Verification
1. Go to: https://myaccount.google.com/security
2. Find "2-Step Verification" section
3. Click "Get Started" or "Turn On"
4. Follow the setup process (you'll need your phone)

### Step 2: Generate App Password
1. Go to: https://myaccount.google.com/apppasswords
2. Select "Mail" from the dropdown
3. Select "Other (Custom name)"
4. Type: "SAC Repository"
5. Click "Generate"
6. **Copy the 16-character password** (it looks like: `abcd efgh ijkl mnop`)

### Step 3: Update Configuration File
1. Open: `git/includes/phpmailer_config.php`
2. Find line 62: `$mail->Password = 'your_app_password_here';`
3. Replace `'your_app_password_here'` with your App Password
4. **Remove spaces** from the password (e.g., `'abcdefghijklmnop'`)
5. Save the file

### Step 4: Test Email Sending
1. Go to: `http://localhost/Arevalo_WS102FInalPeriod/git/test-email.php`
2. Enter your email address
3. Click "Send Test Email"
4. Check your Gmail inbox!

## Example Configuration

**Before:**
```php
$mail->Password = 'your_app_password_here';
```

**After:**
```php
$mail->Password = 'abcdefghijklmnop'; // Your 16-character App Password (no spaces)
```

## Troubleshooting

### Email Still Not Sending?

1. **Check Password Format**
   - Must be exactly 16 characters
   - No spaces
   - Wrapped in single quotes: `'password'`

2. **Verify 2-Step Verification**
   - Make sure it's enabled on your Gmail account
   - App Passwords only work with 2-Step Verification enabled

3. **Check PHP Error Logs**
   - Location: `C:\xampp\php\logs\php_error_log`
   - Look for "PHPMailer" or "sendOTPEmail" messages

4. **Test Connection**
   - Use `test-email.php` page to test
   - It will show detailed error messages

## Important Notes

- ✅ App Password is different from your regular Gmail password
- ✅ App Password is 16 characters (no spaces when copying)
- ✅ Keep your App Password secure - don't share it
- ✅ You can revoke App Passwords anytime from Google Account settings

## Need Help?

If emails still don't send after configuring:
1. Check PHP error logs for specific error messages
2. Verify PHPMailer is installed: `cd git && composer install`
3. Test with `test-email.php` page
4. Make sure Gmail account `neteronet@gmail.com` has 2-Step Verification enabled

