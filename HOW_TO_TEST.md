# How to Test the Email System

## Quick Start

### Option 1: Use the Local Test Server (Recommended)
1. Open a terminal in the `web1` folder
2. Start the test server:
   ```bash
   node local-test-server.js
   ```
3. Open your browser and go to: `http://localhost:8000/contact.html`
4. Fill out the form and submit

### Option 2: Open HTML Files Directly
1. Open `contact.html` directly in your browser
2. Fill out the form and submit
3. **Note**: The form will try to submit to `http://localhost:8000/submit-form.php`
   - If you get a CORS error, start the test server first (Option 1)

## Testing Steps

### 1. Start the Test Server
```bash
cd web1
node local-test-server.js
```

### 2. Test the Form
1. Open `http://localhost:8000/contact.html`
2. Fill in all required fields:
   - Name
   - Email (use a real email to test delivery)
   - Service type
   - Priority level
   - Description
3. Click "SUBMIT REQUEST"

### 3. Check Results
1. **Browser**: You should see a success message with a Ticket ID
2. **Terminal**: The server will show form submission details
3. **Email**: Check the email inbox for the confirmation email
4. **Logs**: Check the `logs/` directory for submission logs

## Troubleshooting

### If you see CORS errors:
- Make sure the test server is running (`node local-test-server.js`)
- Refresh the page after starting the server
- Check that you're accessing via `http://localhost:8000` not `file://`

### If emails aren't being sent:
1. Check that PHPMailer is installed:
   ```bash
   cd web1
   composer require phpmailer/phpmailer
   ```
2. Verify `.env` file has correct SMTP credentials
3. Check `logs/errors.log` for PHP errors
4. Check `logs/email_delivery.log` for email sending attempts

### If PHP errors occur:
1. Check that PHP is installed on your system
2. Verify the `submit-form.php` file has correct syntax
3. Check file permissions for `logs/` directory

## Quick Test Script

You can also run the diagnostic test:
1. Start the test server
2. Open `http://localhost:8000/test-email-system.php`
3. Click "Run Email Test" to test the email configuration

## Production Testing

Before deploying to Hostinger:
1. Update `.env` with real Hostinger SMTP credentials
2. Install PHPMailer on the server
3. Test form submission
4. Verify email delivery
5. Check all log files

## Common Issues & Solutions

### 1. "PHPMailer not found"
- Install PHPMailer: `composer require phpmailer/phpmailer`
- Or upload the `vendor/` directory to your server

### 2. "SMTP Connection Failed"
- Check `.env` file credentials
- Verify Hostinger SMTP settings
- Test port 465 connectivity

### 3. "Permission denied" for logs
- Ensure `logs/` directory exists and is writable
- On Linux/Mac: `chmod 755 logs/`
- On Windows: Check folder permissions

### 4. Form submits but no email
- Check spam folder
- Verify sender email matches SMTP username
- Review `logs/email_delivery.log`

## Support

If you encounter issues:
1. Check the `logs/` directory for error messages
2. Review `INSTALLATION.md` for setup instructions
3. Run `test-email-system.php` for diagnostics
4. Check `COMPLETION_CHECKLIST.md` for deployment steps

The system is now fully functional and ready for production use!
