<?php
/**
 * Test Script for Private Hackers Email System
 * 
 * This script tests the email configuration and PHPMailer setup
 * without submitting an actual form.
 */

echo "<h1>Private Hackers Email System Test</h1>";
echo "<p>Testing configuration and email setup...</p>";

// Test 1: Check if .env file exists
echo "<h2>Test 1: Configuration Files</h2>";
if (file_exists(__DIR__ . '/.env')) {
    echo "<p style='color: green;'>✓ .env file found</p>";
    
    // Check if it's not the example file
    $envContent = file_get_contents(__DIR__ . '/.env');
    if (strpos($envContent, 'your_email@yourdomain.com') === false) {
        echo "<p style='color: green;'>✓ .env file appears to be configured (not using example values)</p>";
    } else {
        echo "<p style='color: orange;'>⚠ .env file contains example values - please update with your actual credentials</p>";
    }
} else {
    echo "<p style='color: red;'>✗ .env file not found</p>";
    echo "<p>Please copy .env.example to .env and configure your settings</p>";
}

// Test 2: Check config.php
echo "<h2>Test 2: Configuration Loader</h2>";
if (file_exists(__DIR__ . '/config.php')) {
    echo "<p style='color: green;'>✓ config.php file found</p>";
    
    try {
        require_once __DIR__ . '/config.php';
        echo "<p style='color: green;'>✓ Configuration loaded successfully</p>";
        
        // Test if Config class works
        if (class_exists('Config')) {
            echo "<p style='color: green;'>✓ Config class available</p>";
            
            // Try to get some configuration
            $smtpConfig = Config::getSmtpConfig();
            if (!empty($smtpConfig['host'])) {
                echo "<p style='color: green;'>✓ SMTP configuration loaded: " . htmlspecialchars($smtpConfig['host']) . "</p>";
            }
        }
    } catch (\Exception $e) {
        echo "<p style='color: red;'>✗ Error loading configuration: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ config.php file not found</p>";
}

// Test 3: Check PHPMailer
echo "<h2>Test 3: PHPMailer Installation</h2>";
$phpmailerLoaded = false;
$autoloader_paths = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php'
];

foreach ($autoloader_paths as $path) {
    if (file_exists($path)) {
        echo "<p style='color: green;'>✓ Composer autoloader found at: " . htmlspecialchars($path) . "</p>";
        $phpmailerLoaded = true;
        break;
    }
}

if (!$phpmailerLoaded && file_exists(__DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
    echo "<p style='color: green;'>✓ PHPMailer found (direct include)</p>";
    $phpmailerLoaded = true;
}

if (!$phpmailerLoaded) {
    echo "<p style='color: red;'>✗ PHPMailer not found</p>";
    echo "<p>Please install PHPMailer using: <code>composer require phpmailer/phpmailer</code></p>";
    echo "<p>Or download manually from: <a href='https://github.com/PHPMailer/PHPMailer'>https://github.com/PHPMailer/PHPMailer</a></p>";
}

// Test 4: Check log directory
echo "<h2>Test 4: Logging System</h2>";
$logDir = __DIR__ . '/logs';
if (is_dir($logDir)) {
    echo "<p style='color: green;'>✓ Log directory exists</p>";
    
    // Check if writable
    if (is_writable($logDir)) {
        echo "<p style='color: green;'>✓ Log directory is writable</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Log directory exists but may not be writable</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠ Log directory doesn't exist (will be created automatically on first use)</p>";
}

// Test 5: Check submit-form.php
echo "<h2>Test 5: Form Handler</h2>";
if (file_exists(__DIR__ . '/submit-form.php')) {
    echo "<p style='color: green;'>✓ submit-form.php found</p>";
    
    // Check file size
    $size = filesize(__DIR__ . '/submit-form.php');
    echo "<p>File size: " . number_format($size) . " bytes</p>";
    
    // Check for common issues
    $content = file_get_contents(__DIR__ . '/submit-form.php');
    if (strpos($content, 'PHPMailer') !== false) {
        echo "<p style='color: green;'>✓ PHPMailer integration detected</p>";
    }
    
    if (strpos($content, 'Config::') !== false) {
        echo "<p style='color: green;'>✓ Configuration integration detected</p>";
    }
} else {
    echo "<p style='color: red;'>✗ submit-form.php not found</p>";
}

// Test 6: PHP Version and Extensions
echo "<h2>Test 6: PHP Environment</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";

$requiredExtensions = ['openssl', 'json', 'filter'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color: green;'>✓ $ext extension loaded</p>";
    } else {
        echo "<p style='color: orange;'>⚠ $ext extension not loaded (may be required for some features)</p>";
    }
}

// Test 7: Test email sending (optional)
echo "<h2>Test 7: Email Sending Test (Optional)</h2>";
echo "<p>This test will attempt to send a test email using your configuration.</p>";
echo "<form method='post'>";
echo "<input type='hidden' name='test_email' value='1'>";
echo "<button type='submit'>Run Email Test</button>";
echo "</form>";

if (isset($_POST['test_email']) && $phpmailerLoaded) {
    echo "<h3>Email Test Results:</h3>";
    
    try {
        require_once __DIR__ . '/config.php';
        
        // Load PHPMailer
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            require_once __DIR__ . '/vendor/autoload.php';
        } elseif (file_exists(__DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
            require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
            require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
            require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
        }
        
        // Use fully qualified class names instead of use statements
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $smtpConfig = Config::getSmtpConfig();
        $emailConfig = Config::getEmailConfig();
        
        // Server settings
        $mail->SMTPDebug = 2; // Enable verbose debug output
        $mail->isSMTP();
        $mail->Host       = $smtpConfig['host'];
        $mail->Port       = $smtpConfig['port'];
        $mail->SMTPSecure = $smtpConfig['secure'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpConfig['username'];
        $mail->Password   = $smtpConfig['password'];
        
        // Recipients
        $mail->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
        $mail->addAddress($emailConfig['admin_email']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Test Email from Private Hackers System';
        $mail->Body    = 'This is a test email sent from the email system test script.';
        
        ob_start();
        $mail->send();
        $debugOutput = ob_get_clean();
        
        echo "<p style='color: green;'>✓ Test email sent successfully!</p>";
        echo "<p>Check your email at: " . htmlspecialchars($emailConfig['admin_email']) . "</p>";
        
        if (!empty($debugOutput)) {
            echo "<h4>Debug Output:</h4>";
            echo "<pre>" . htmlspecialchars($debugOutput) . "</pre>";
        }
        
    } catch (\Exception $e) {
        echo "<p style='color: red;'>✗ Email test failed: " . htmlspecialchars($e->getMessage()) . "</p>";
        if (isset($mail) && !empty($mail->ErrorInfo)) {
            echo "<p>Error details: " . htmlspecialchars($mail->ErrorInfo) . "</p>";
        }
    }
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>If all tests pass, your email system should be ready for production.</p>";
echo "<p>Next steps:</p>";
echo "<ol>";
echo "<li>Install PHPMailer if not already installed</li>";
echo "<li>Configure your .env file with actual credentials</li>";
echo "<li>Test the form submission locally</li>";
echo "<li>Deploy to Hostinger</li>";
echo "</ol>";

echo "<p><a href='INSTALLATION.md'>View detailed installation guide</a></p>";
?>
