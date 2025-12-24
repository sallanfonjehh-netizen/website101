# Hostinger Deployment Guide for Private Hackers Website

## Current Issue
The form is trying to submit to `https://privatehakers.com/submit-form.php` but getting a **403 Forbidden** error. This means the PHP files are not properly uploaded or configured on the live server.

## Step-by-Step Deployment Instructions

### 1. Upload All Files to Hostinger

**Using Hostinger File Manager:**
1. Log into your Hostinger Control Panel
2. Go to **File Manager**
3. Navigate to your website's root directory (usually `public_html` or `htdocs`)
4. **Create a new folder** called `web1` (or use your preferred name)
5. **Upload ALL files** from your local `web1` folder to the server:
   - `.env.example` (rename to `.env` after upload)
   - `.htaccess`
   - `config.php`
   - `submit-form.php`
   - `install-phpmailer.php`
   - `test-hostinger-email.php`
   - `test-email-system.php`
   - All HTML files (`index.html`, `contact.html`, etc.)
   - All CSS/JS files
   - Create `logs/` directory (with proper permissions)

### 2. Set File Permissions

**Required Permissions:**
- PHP files: **644** (read/write for owner, read for others)
- `.env` file: **600** (read/write for owner only - for security)
- `logs/` directory: **755** (read/write/execute for owner, read/execute for others)

**How to set permissions in Hostinger File Manager:**
1. Right-click on each file/folder
2. Select **"Change Permissions"**
3. Set the appropriate permissions
4. Click **"Change"**

### 3. Configure `.env` File

1. In File Manager, locate `.env.example`
2. **Copy it** to create `.env`
3. **Edit `.env`** and update with your actual Hostinger SMTP credentials:
   ```
   SMTP_HOST=smtp.hostinger.com
   SMTP_PORT=587
   SMTP_SECURE=tls
   SMTP_USERNAME=contact@privatehakers.com
   SMTP_PASSWORD=YourActualPasswordHere
   FROM_EMAIL=contact@privatehakers.com
   FROM_NAME=Private Hackers Support
   ADMIN_EMAIL=contact@privatehakers.com
   ```

### 4. Install PHPMailer

**Option A: Upload PHPMailer Manually (Recommended)**
1. Download PHPMailer from: https://github.com/PHPMailer/PHPMailer
2. Extract the ZIP file
3. Upload the `src` folder to `web1/PHPMailer/` on your server
4. Should create: `web1/PHPMailer/src/PHPMailer.php`

**Option B: Use Hostinger's Composer**
1. In File Manager, create `composer.json` in `web1/` folder:
   ```json
   {
     "require": {
       "phpmailer/phpmailer": "^6.8"
     }
   }
   ```
2. Use Hostinger's terminal or auto-installer to run `composer install`

### 5. Test the Installation

**Test 1: Check PHP Files**
1. Open: `https://privatehakers.com/web1/install-phpmailer.php`
2. Follow the instructions to verify PHPMailer installation

**Test 2: Test Email System**
1. Open: `https://privatehakers.com/web1/test-hostinger-email.php`
2. Enter your email and send a test email

**Test 3: Test Contact Form**
1. Open: `https://privatehakers.com/web1/contact.html`
2. Fill out the form and submit
3. Check if you get a success message

### 6. Fix Common 403 Forbidden Errors

**Possible Causes and Solutions:**

1. **File doesn't exist on server:**
   - Upload `submit-form.php` to the correct location

2. **Incorrect file permissions:**
   - Set PHP files to **644**
   - Set `.env` to **600**
   - Set directories to **755**

3. **Server security restrictions:**
   - Check Hostinger's security settings
   - Ensure PHP is enabled for the directory
   - Check if mod_security is blocking POST requests

4. **.htaccess issues:**
   - Ensure `.htaccess` file is uploaded
   - Check for any rewrite rules that might block PHP files

5. **PHP version mismatch:**
   - Check PHP version in Hostinger (should be 7.4 or higher)
   - Update PHP version if needed

### 7. Verify Deployment

**Checklist:**
- [ ] All files uploaded to `web1/` directory on server
- [ ] `.env` file created with correct SMTP credentials
- [ ] PHPMailer installed (either via upload or Composer)
- [ ] File permissions set correctly
- [ ] `logs/` directory exists and is writable
- [ ] PHP version is 7.4 or higher
- [ ] Test scripts work (`install-phpmailer.php`, `test-hostinger-email.php`)
- [ ] Contact form submits without 403 error

### 8. Troubleshooting

**If you still get 403 Forbidden:**

1. **Check Hostinger error logs:**
   - Go to Hostinger Control Panel → Logs
   - Look for any access denied errors

2. **Test with simple PHP file:**
   Create `test.php` with:
   ```php
   <?php
   echo "PHP is working!";
   ?>
   ```
   Access it via browser to ensure PHP is working

3. **Check server configuration:**
   - Ensure `AllowOverride All` is set in Apache configuration
   - Check for any IP blocking or security rules

4. **Contact Hostinger Support:**
   - Provide them with the 403 error details
   - Ask about any security restrictions on PHP files

### 9. Final Verification

Once everything is working:
1. **Delete test files** (optional but recommended for security):
   - `install-phpmailer.php`
   - `test-hostinger-email.php`
   - `test-email-system.php`

2. **Monitor logs** for any issues:
   - Check `web1/logs/` directory for error logs
   - Monitor email delivery logs

3. **Test complete workflow:**
   - Submit contact form
   - Check if confirmation email is received
   - Verify admin notification email is sent

## Quick Fix for Immediate Testing

If you need to test immediately while fixing the 403 error, the form has a **fallback mechanism**:
1. The form will still submit locally
2. A ticket ID will be generated and stored in browser
3. You can check the ticket status using the tracker
4. Once PHP is working, emails will be sent automatically

The form is designed to work even when the PHP backend is unavailable, so users can still submit requests while you fix the server configuration.
