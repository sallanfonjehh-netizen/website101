<?php
/**
 * Test Hostinger Email Configuration
 * 
 * This script tests the email system with Hostinger's recommended settings
 * Run this script after installing PHPMailer to verify everything works
 */

echo "<h1>Hostinger Email System Test</h1>";

// Check if PHPMailer is installed
$phpmailer_paths = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/PHPMailer/src/PHPMailer.php',
    __DIR__ . '/phpmailer/src/PHPMailer.php'
];

$phpmailer_loaded = false;
$phpmailer_path = '';

foreach ($phpmailer_paths as $path) {
    if (file_exists($path)) {
        $phpmailer_path = $path;
        try {
            if (strpos($path, 'autoload.php') !== false) {
                require_once $path;
            } else {
                require_once dirname($path) . '/Exception.php';
                require_once $path;
                require_once dirname($path) . '/SMTP.php';
            }
            $phpmailer_loaded = true;
            break;
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Error loading PHPMailer from $path: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

if (!$phpmailer_loaded) {
    echo "<p style='color: red;'>✗ PHPMailer not found. Please install it first.</p>";
    echo "<p>Installation paths checked:</p>";
    echo "<ul>";
    foreach ($phpmailer_paths as $path) {
        echo "<li>" . htmlspecialchars($path) . " - " . (file_exists($path) ? "Exists" : "Not found") . "</li>";
    }
    echo "</ul>";
    exit();
}

echo "<p style='color: green;'>✓ PHPMailer loaded from: " . htmlspecialchars($phpmailer_path) . "</p>";

// Check .env file
if (!file_exists(__DIR__ . '/.env')) {
    echo "<p style='color: red;'>✗ .env file not found. Copy .env.example to .env and configure your SMTP settings.</p>";
    exit();
}

// Parse .env file
$env_content = file_get_contents(__DIR__ . '/.env');
$lines = explode("\n", $env_content);
$config = [];

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || $line[0] === '#') {
        continue;
    }
    
    $parts = explode('=', $line, 2);
    if (count($parts) === 2) {
        $config[trim($parts[0])] = trim($parts[1]);
    }
}

// Check required configuration
$required_config = [
    'SMTP_HOST' => 'smtp.hostinger.com',
    'SMTP_PORT' => '587',
    'SMTP_SECURE' => 'tls',
    'SMTP_USERNAME' => 'contact@privatehakers.com',
    'SMTP_PASSWORD' => 'Your password',
    'FROM_EMAIL' => 'contact@privatehakers.com',
    'FROM_NAME' => 'Private Hackers Support',
    'ADMIN_EMAIL' => 'contact@privatehakers.com'
];

echo "<h2>Configuration Check</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Setting</th><th>Expected</th><th>Actual</th><th>Status</th></tr>";

foreach ($required_config as $key => $expected) {
    $actual = isset($config[$key]) ? $config[$key] : 'Not set';
    $status = '⚠';
    $color = 'orange';
    
    if ($key === 'SMTP_PASSWORD') {
        $display_actual = str_repeat('*', min(strlen($actual), 8)) . '...';
    } else {
        $display_actual = htmlspecialchars($actual);
    }
    
    if ($actual === $expected) {
        $status = '✓';
        $color = 'green';
    } elseif ($actual === 'Not set') {
        $status = '✗';
        $color = 'red';
    }
    
    echo "<tr>";
    echo "<td>$key</td>";
    echo "<td>" . htmlspecialchars($expected) . "</td>";
    echo "<td>$display_actual</td>";
    echo "<td style='color: $color;'>$status</td>";
    echo "</tr>";
}

echo "</table>";

// Test email sending
echo "<h2>Email Sending Test</h2>";

if (isset($_POST['test_email'])) {
    $test_email = $_POST['test_email'];
    
    if (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
        echo "<p style='color: red;'>✗ Invalid email address: " . htmlspecialchars($test_email) . "</p>";
    } else {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->SMTPDebug = 2; // Enable verbose debug output
            $mail->isSMTP();
            $mail->Host       = $config['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['SMTP_USERNAME'];
            $mail->Password   = $config['SMTP_PASSWORD'];
            $mail->SMTPSecure = $config['SMTP_SECURE'];
            $mail->Port       = $config['SMTP_PORT'];
            
            // Recipients
            $mail->setFrom($config['FROM_EMAIL'], $config['FROM_NAME']);
            $mail->addAddress($test_email);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Test Email from Private Hackers';
            $mail->Body    = '<h1>Test Email Successful!</h1>
                            <p>This is a test email from your Private Hackers website.</p>
                            <p>If you received this email, your Hostinger SMTP configuration is working correctly.</p>
                            <p><strong>Timestamp:</strong> ' . date('Y-m-d H:i:s') . '</p>';
            $mail->AltBody = 'Test Email Successful! This is a test email from your Private Hackers website.';
            
            echo "<pre style='background: #f5f5f5; padding: 10px;'>";
            echo "Attempting to send test email...\n";
            echo "================================\n";
            
            // Capture debug output
            ob_start();
            $mail->send();
            $debug_output = ob_get_clean();
            
            echo htmlspecialchars($debug_output);
            echo "</pre>";
            
            echo "<p style='color: green;'>✓ Test email sent successfully to: " . htmlspecialchars($test_email) . "</p>";
            echo "<p>Check your inbox (and spam folder) for the test email.</p>";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Email sending failed: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p>Error details: " . htmlspecialchars($mail->ErrorInfo) . "</p>";
            
            echo "<h3>Troubleshooting Tips:</h3>";
            echo "<ol>";
            echo "<li>Verify your SMTP credentials in the .env file</li>";
            echo "<li>Make sure your Hostinger email account is active</li>";
            echo "<li>Check if port 587 is open (Hostinger uses port 587 with TLS)</li>";
            echo "<li>Verify DNS records (MX, SPF, DKIM) are correctly set</li>";
            echo "<li>Try using the email address as both sender and recipient for testing</li>";
            echo "</ol>";
        }
    }
}

// Test form
echo "<h3>Send a Test Email</h3>";
echo "<form method='post'>";
echo "<p>Enter an email address to send a test email:</p>";
echo "<input type='email' name='test_email' value='" . htmlspecialchars($config['ADMIN_EMAIL'] ?? '') . "' required style='padding: 5px; width: 300px;'>";
echo "<button type='submit' style='padding: 5px 15px; margin-left: 10px;'>Send Test Email</button>";
echo "</form>";

echo "<hr>";
echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>If the test email works, your contact form should work too</li>";
echo "<li>Test the contact form at <a href='contact.html'>contact.html</a></li>";
echo "<li>Check the logs directory for any errors</li>";
echo "<li>Delete this test script after verification</li>";
echo "</ol>";

echo "<p><strong>Note:</strong> This script is for testing purposes only. Delete it after verification.</p>";
?>
