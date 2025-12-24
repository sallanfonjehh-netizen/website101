# Email Handling System - Manual Testing Guide

## Overview
This guide provides step-by-step instructions for manually testing the email handling system for the Private Hackers website. The system uses PHPMailer with Hostinger SMTP to send emails reliably.

## Prerequisites

### 1. Hostinger Environment Setup
- Hostinger Business Hosting account
- Email account created: `contactus@privatehackers.com`
- SMTP credentials configured:
  - Host: `smtp.hostinger.com`
  - Port: `465`
  - Encryption: `SSL`
  - Username: `contactus@privatehackers.com`
  - Password: `[Your actual password]`

### 2. PHP Requirements
- PHP 7.4 or higher
- PHPMailer installed via Composer
- `composer.json` file present in project root

### 3. File Structure Verification
Ensure these files exist:
```
/
├── submit-form.php          # Main form handler
├── composer.json            # PHPMailer dependencies
├── logs/                    # Log directory
│   ├── errors.log          # Error logs
│   ├── email_delivery.log  # Email delivery logs
│   ├── submissions.log     # Form submission logs
│   └── debug.log           # Debug logs
└── EMAIL_TESTING_GUIDE.md  # This file
```

## Installation Steps (Before Testing)

### Step 1: Install PHPMailer via Composer
```bash
# SSH into Hostinger server
cd /path/to/website
composer install
```

### Step 2: Verify Installation
```bash
# Check if vendor directory exists
ls -la vendor/
# Should see phpmailer directory
```

### Step 3: Set File Permissions
```bash
# Set proper permissions for logs directory
chmod 755 logs/
chmod 644 logs/*.log
```

## Manual Testing Procedures

### Test 1: Basic Form Submission

#### Objective
Verify that the form can be submitted successfully and returns a valid JSON response.

#### Steps:
1. Open `contact.html` in a web browser
2. Fill out all required fields:
   - Name: `Test User`
   - Email: `test@example.com` (use a real email you can check)
   - Phone: `+1 (555) 123-4567`
   - Service: `Phone Access`
   - Priority: `High`
   - Description: `Testing email functionality`
3. Click "Submit Request"
4. Check browser console for response

#### Expected Results:
- JSON response with `success: true`
- Ticket ID generated (format: `PRIVATE-2025-XXXXXX`)
- Success message displayed to user
- No JavaScript errors in console

### Test 2: Email Delivery Verification

#### Objective
Verify that emails are sent successfully via SMTP.

#### Steps:
1. Submit the form using a real email address you can access
2. Check both email inboxes:
   - **Client Email**: The email address you entered in the form
   - **Admin Email**: `contactus@privatehackers.com`

#### Expected Results:
1. **Client Email** should receive:
   - Subject: `Request Confirmation - Ticket ID: [TICKET_ID]`
   - HTML email with ticket details
   - Professional formatting with company branding

2. **Admin Email** should receive:
   - Subject: `[PRIVATE HACKERS] New Service Request - Ticket: [TICKET_ID]`
   - HTML email with full submission details
   - All form data clearly presented

### Test 3: Log File Verification

#### Objective
Verify that all actions are properly logged.

#### Steps:
1. After form submission, check log files:
   ```bash
   # SSH into server
   cd /path/to/website/logs
   ls -la
   ```

2. Examine each log file:
   ```bash
   # Check error logs
   tail -20 errors.log
   
   # Check email delivery logs
   tail -20 email_delivery.log
   
   # Check submission logs
   tail -20 submissions.log
   
   # Check debug logs
   tail -20 debug.log
   ```

#### Expected Results:
- `errors.log`: Should be empty or contain only expected warnings
- `email_delivery.log`: Should show `SUCCESS` entries for both client and admin emails
- `submissions.log`: Should show the submission with all details
- `debug.log`: Should show detailed debugging information

### Test 4: Error Handling Tests

#### Test 4A: Invalid Email Address
1. Submit form with invalid email: `invalid-email`
2. Check response

**Expected**: JSON response with `success: false` and error message about invalid email

#### Test 4B: Missing Required Fields
1. Submit form with empty required fields
2. Check response

**Expected**: JSON response with `success: false` and specific field error messages

#### Test 4C: SMTP Failure Simulation
1. Temporarily change SMTP password to incorrect value in `submit-form.php`
2. Submit form
3. Check logs

**Expected**: 
- `errors.log` should contain SMTP authentication errors
- `email_delivery.log` should show `FAILED` status
- User should still receive success response (errors are logged but not shown to user)

### Test 5: Log Viewing Interface

#### Objective
Verify that log viewing works correctly.

#### Steps:
1. Access `logs/view.php` in browser
2. Check if logs are displayed properly
3. Verify .htaccess protection is working

#### Expected Results:
- Logs should be displayed in readable format
- Direct access to log files should be blocked by .htaccess
- No PHP errors on the page

## Troubleshooting Common Issues

### Issue 1: Emails Not Sending
**Symptoms**: Form submits successfully but no emails received.

**Diagnosis Steps**:
1. Check `email_delivery.log` for status
2. Check `errors.log` for SMTP errors
3. Verify SMTP credentials in `submit-form.php`
4. Test SMTP connection manually:
   ```bash
   telnet smtp.hostinger.com 465
   ```

**Solutions**:
- Verify SMTP username/password are correct
- Check if email account exists on Hostinger
- Ensure port 465 is not blocked by firewall
- Verify FROM email matches authenticated username

### Issue 2: PHPMailer Not Found
**Symptoms**: Error in logs about PHPMailer class not found.

**Solutions**:
```bash
# Reinstall PHPMailer
composer require phpmailer/phpmailer

# Or manually check vendor directory
ls -la vendor/phpmailer/phpmailer/
```

### Issue 3: Permission Errors
**Symptoms**: Cannot write to log files.

**Solutions**:
```bash
# Fix permissions
chmod 755 logs/
chmod 644 logs/*.log
chown www-data:www-data logs/ -R  # Adjust user/group as needed
```

### Issue 4: JSON Response Errors
**Symptoms**: Browser shows "Unexpected token" or JSON parse error.

**Solutions**:
1. Check for PHP warnings/errors before JSON output
2. Enable error logging in PHP
3. Test with `curl` to see raw response:
   ```bash
   curl -X POST https://yourdomain.com/submit-form.php \
     -H "Content-Type: application/json" \
     -d '{"name":"Test","email":"test@example.com","phone":"1234567890","service":"test","priority":"medium","description":"test"}'
   ```

## Performance Testing

### Test 6: Concurrent Submissions
**Objective**: Test system under load.

**Steps**:
1. Submit 5 forms within 10 seconds
2. Monitor log files
3. Check email delivery times

**Expected**: All submissions should be processed without errors.

### Test 7: Large Form Data
**Objective**: Test with maximum data.

**Steps**:
1. Submit form with very long description (5000+ characters)
2. Submit form with many features selected

**Expected**: System should handle large data without issues.

## Security Testing

### Test 8: XSS Protection
**Steps**:
1. Submit form with HTML/JavaScript in fields:
   ```json
   {
     "name": "<script>alert('xss')</script>",
     "email": "test@example.com",
     "phone": "1234567890",
     "service": "test",
     "priority": "medium",
     "description": "test"
   }
   ```

**Expected**: HTML should be escaped in emails and logs.

### Test 9: SQL Injection Protection
**Note**: This system doesn't use SQL database, but test anyway.

**Steps**:
1. Submit form with SQL injection attempts in fields

**Expected**: Data should be sanitized and treated as plain text.

## Deployment Verification Checklist

After deploying to Hostinger, verify:

- [ ] `composer install` completed successfully
- [ ] `vendor/` directory exists with PHPMailer
- [ ] `logs/` directory exists and is writable
- [ ] SMTP credentials are correct in `submit-form.php`
- [ ] `.htaccess` in logs directory blocks direct access
- [ ] All HTML pages link to correct form handler
- [ ] JavaScript validation works on frontend
- [ ] Form submits without JavaScript errors
- [ ] Emails are delivered to both client and admin
- [ ] Logs are being written correctly
- [ ] No PHP errors in error logs
- [ ] Response time is acceptable (< 5 seconds)

## Log Analysis Guide

### Understanding Log Formats

#### Error Log (`errors.log`)
```
[2025-12-15 12:30:45] [ERROR] [REQUEST_ID: REQ-20251215123045-ABCD1234] [IP: 192.168.1.1] Error message here | Context: {"key":"value"}
```

#### Email Delivery Log (`email_delivery.log`)
```
[2025-12-15 12:30:45] Ticket: PRIVATE-2025-123456 | To: test@example.com | Method: SMTP | Status: SUCCESS | Subject: Request Confirmation - Ticket ID: PRIVATE-2025-123456 | IP: 192.168.1.1
```

#### Submission Log (`submissions.log`)
```
[2025-12-15 12:30:45] Ticket: PRIVATE-2025-123456 | Name: Test User | Email: test@example.com | Service: Phone Access | Priority: High | IP: 192.168.1.1
```

### Common Log Patterns to Monitor

1. **SMTP Authentication Failures**: Look for `535 Authentication failed` in errors.log
2. **PHPMailer Exceptions**: Look for `PHPMailer Exception:` in errors.log
3. **Email Delivery Failures**: Look for `Status: FAILED` in email_delivery.log
4. **Validation Errors**: Look for `VALIDATION_ERROR` in errors.log

## Support Contact

If issues persist after following this guide:

1. Check Hostinger Knowledge Base for SMTP setup
2. Contact Hostinger Support for SMTP/email issues
3. Review PHPMailer documentation: https://github.com/PHPMailer/PHPMailer

## Version History

- v1.0 (2025-12-15): Initial testing guide created
- Includes comprehensive manual testing procedures
- Covers all acceptance criteria from requirements
