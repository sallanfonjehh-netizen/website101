# PHPMailer Installation Guide for Hostinger

## Prerequisites
- PHP 7.4 or higher
- Composer (PHP package manager)
- Hostinger Business Hosting with SMTP access

## Installation Steps

### 1. Install Composer (if not already installed)

**On Windows:**
```bash
# Download and run Composer-Setup.exe from https://getcomposer.org/download/
```

**On Linux/Mac:**
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Install PHPMailer via Composer

Navigate to your website directory and run:

```bash
cd /path/to/your/website/web1
composer require phpmailer/phpmailer
```

This will:
- Create a `vendor` directory
- Install PHPMailer and its dependencies
- Generate `vendor/autoload.php`

### 3. Configure Environment Variables

1. Copy the example environment file:
   ```bash
   cp .env.example .env
   ```

2. Edit the `.env` file with your Hostinger SMTP credentials:
   ```bash
   # Open in text editor
   nano .env
   ```

3. Update these values:
   ```
   SMTP_USERNAME=your_email@yourdomain.com
   SMTP_PASSWORD=your_smtp_password
   FROM_EMAIL=your_email@yourdomain.com
   ADMIN_EMAIL=your_email@yourdomain.com
   ```

### 4. Verify Installation

Create a test script `test-email.php`:

```php
<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->Port = 465;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->SMTPAuth = true;
    $mail->Username = 'your_email@yourdomain.com';
    $mail->Password = 'your_password';
    
    // Recipients
    $mail->setFrom('your_email@yourdomain.com', 'Test');
    $mail->addAddress('test@example.com');
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body = 'This is a test email from PHPMailer';
    
    $mail->send();
    echo 'Email sent successfully';
} catch (Exception $e) {
    echo "Error: {$mail->ErrorInfo}";
}
?>
```

### 5. File Permissions

Ensure proper file permissions:
```bash
chmod 755 logs/
chmod 644 .env
chmod 644 config.php
chmod 644 submit-form.php
```

### 6. Test the Form

1. Start the local test server:
   ```bash
   node local-test-server.js
   ```

2. Open `http://localhost:8000` in your browser
3. Submit the contact form
4. Check the terminal for submission logs
5. Check email inboxes for confirmation emails

## Troubleshooting

### Common Issues

1. **PHPMailer not found error**
   - Run `composer install` in the web1 directory
   - Verify `vendor/autoload.php` exists

2. **SMTP Connection Failed**
   - Verify SMTP credentials in `.env`
   - Check if port 465 is open on Hostinger
   - Ensure SSL is enabled

3. **Email not delivered**
   - Check spam/junk folders
   - Verify sender email matches SMTP username
   - Check email logs in `logs/email_delivery.log`

4. **Permission denied errors**
   - Ensure `logs/` directory is writable
   - Check file permissions: `ls -la`

### Log Files Location
- Error logs: `logs/errors.log`
- Email delivery logs: `logs/email_delivery.log`
- Submission logs: `logs/submissions.log`

### Hostinger SMTP Settings
- Host: `smtp.hostinger.com`
- Port: `465` (SSL) or `587` (TLS)
- Encryption: `SSL` or `TLS`
- Authentication: Required
- Username: Full email address
- Password: Email account password

## Support

If issues persist:
1. Check Hostinger email settings in control panel
2. Verify email account is active and not blocked
3. Contact Hostinger support for SMTP configuration
4. Review PHP error logs on Hostinger
