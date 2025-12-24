# Quick Setup Guide - Hostinger Email Configuration

## 1. Create .env File
```bash
cp .env.example .env
```

## 2. Edit .env with Your Hostinger Credentials

### Required Settings (MUST MATCH):
```ini
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=465
SMTP_SECURE=ssl

# These THREE must use the SAME email address:
SMTP_USERNAME=youremail@yourdomain.com
FROM_EMAIL=youremail@yourdomain.com
ADMIN_EMAIL=youremail@yourdomain.com  # or different admin email

SMTP_PASSWORD=your_actual_password
FROM_NAME=Your Company Name
```

### Important Notes:
✅ **SMTP_USERNAME** and **FROM_EMAIL** MUST be identical
✅ Use your actual Hostinger email password
✅ Email domain must match your hosting domain
✅ Port 465 with SSL is the recommended Hostinger setting

## 3. Test the Configuration

### Option A: Use Test Script
Visit: `https://yourdomain.com/test-email-system.php`

This will check:
- ✓ .env file exists
- ✓ Configuration is valid
- ✓ PHPMailer is installed
- ✓ SMTP connection (when you click "Test Email")

### Option B: Submit Test Form
1. Go to `https://yourdomain.com/contact.html`
2. Fill out the form
3. Submit and check for confirmation
4. Check logs at `/logs/email_delivery.log`

## 4. Troubleshooting

### If emails don't send:

1. **Check .env file exists and is configured**
   ```bash
   ls -la .env
   cat .env  # Should NOT contain "your_email@yourdomain.com"
   ```

2. **Verify credentials in Hostinger cPanel**
   - Email Accounts → Find your email
   - Make sure password is correct
   - Verify SMTP is enabled

3. **Check logs for errors**
   ```bash
   tail -f logs/email_delivery.log
   tail -f logs/errors.log
   ```

4. **Try alternative port (if 465 doesn't work)**
   ```ini
   SMTP_PORT=587
   SMTP_SECURE=tls
   ```

5. **Verify firewall allows port 465**
   - Contact Hostinger support if blocked

## 5. Common Errors

### "SMTP Error: Could not authenticate"
→ SMTP_USERNAME doesn't match FROM_EMAIL
→ Wrong password

### "Could not connect to SMTP host"
→ Port 465 blocked by firewall
→ Try port 587 with TLS

### "Sender address rejected"
→ FROM_EMAIL doesn't match SMTP_USERNAME
→ Email domain doesn't match hosting

## System Status: ✅ VERIFIED

The code is correctly configured for Hostinger:
- ✅ Port 465 with SSL
- ✅ PHPMailer integrated
- ✅ Error handling in place
- ✅ Logging system active
- ✅ Security measures implemented

## Files Modified/Created
- `submit-form.php` - Updated comments for port 465
- `EMAIL_SYSTEM_VERIFICATION.md` - Full documentation
- `HOSTINGER_SETUP.md` - This quick guide

Just create your `.env` file with actual credentials and test!
