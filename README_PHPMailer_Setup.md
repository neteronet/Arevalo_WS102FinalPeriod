# PHPMailer Setup Instructions

## Installation

1. **Download PHPMailer** (if not using Composer):
   - Download from: https://github.com/PHPMailer/PHPMailer
   - Extract to: `git/vendor/phpmailer/phpmailer/`

2. **Or use Composer** (Recommended):
   ```bash
   cd git
   composer require phpmailer/phpmailer
   ```

## Gmail Configuration

1. **Enable 2-Step Verification** on your Gmail account (neteronet@gmail.com)

2. **Generate App Password**:
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" and "Other (Custom name)"
   - Enter "SAC Repository" as the name
   - Copy the 16-character app password

3. **Update PHPMailer Config**:
   - Open: `git/includes/phpmailer_config.php`
   - Replace `'your_app_password_here'` with your Gmail App Password on line 25

## Database Setup

Run the SQL file to create the password_resets table:
```sql
-- Run: git/database_password_reset.sql
```

## Files Created

1. **Student Pages**:
   - `git/pages/student/forgot-password.php` - Request OTP
   - `git/pages/student/reset-password.php` - Reset password with OTP

2. **Supervisor Pages**:
   - `git/pages/supervisor/forgot-password.php` - Request OTP
   - `git/pages/supervisor/reset-password.php` - Reset password with OTP

3. **Configuration**:
   - `git/includes/phpmailer_config.php` - PHPMailer setup
   - `git/database_password_reset.sql` - Database table

## How It Works

1. User clicks "Forgot Password?" on login page
2. Enters email address
3. System generates 6-digit OTP and sends via email
4. User enters OTP and new password
5. System verifies OTP and updates password
6. OTP expires after 15 minutes

## Security Features

- OTP expires in 15 minutes
- OTP can only be used once
- Tokens are unique and secure
- Password hashing with bcrypt
- Email validation

## Testing

1. Make sure PHPMailer is installed
2. Update Gmail App Password in config
3. Run database SQL
4. Test forgot password flow

