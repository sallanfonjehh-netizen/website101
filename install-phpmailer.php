<?php
/**
 * PHPMailer Installation Helper
 * 
 * This script helps install PHPMailer on Hostinger hosting
 * Run this script once via browser or SSH to install PHPMailer
 */

echo "<h1>PHPMailer Installation Helper</h1>";

// Check if vendor directory exists
if (is_dir(__DIR__ . '/vendor')) {
    echo "<p style='color: green;'>✓ PHPMailer is already installed (vendor directory exists)</p>";
    
    // Check if autoloader works
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        echo "<p style='color: green;'>✓ Autoloader found at vendor/autoload.php</p>";
        
        // Test PHPMailer loading
        try {
            require_once __DIR__ . '/vendor/autoload.php';
            echo "<p style='color: green;'>✓ PHPMailer classes loaded successfully</p>";
            
            // Test PHPMailer instantiation
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            echo "<p style='color: green;'>✓ PHPMailer instantiated successfully</p>";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Error loading PHPMailer: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Autoloader not found at vendor/autoload.php</p>";
    }
    
    exit();
}

echo "<h2>Installation Options</h2>";

echo "<h3>Option 1: Manual Installation via SSH</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px;'>";
echo "1. Connect to your Hostinger account via SSH\n";
echo "2. Navigate to your website directory:\n";
echo "   cd /path/to/your/website/web1\n";
echo "3. Install Composer (if not installed):\n";
echo "   curl -sS https://getcomposer.org/installer | php\n";
echo "4. Install PHPMailer:\n";
echo "   php composer.phar require phpmailer/phpmailer\n";
echo "5. Verify installation:\n";
echo "   ls -la vendor/\n";
echo "</pre>";

echo "<h3>Option 2: Download PHPMailer Manually (Hostinger's Recommended Method)</h3>";
echo "<p>If you don't have SSH access, you can download PHPMailer manually:</p>";
echo "<pre style='background: #f5f5f5; padding: 10px;'>";
echo "1. Download PHPMailer from: https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.8.0.zip\n";
echo "2. Extract the ZIP file\n";
echo "3. Upload the 'src' folder to your website as 'PHPMailer' (capital P)\n";
echo "4. The structure should be: web1/PHPMailer/src/PHPMailer.php\n";
echo "5. The script will automatically find it at: " . __DIR__ . "/PHPMailer/src/PHPMailer.php\n";
echo "</pre>";
echo "<p><strong>Important:</strong> The folder must be named 'PHPMailer' (with capital P) for the script to find it automatically.</p>";

echo "<h3>Option 3: Use Hostinger File Manager (Easiest)</h3>";
echo "<p>You can also install via Hostinger File Manager:</p>";
echo "<ol>";
echo "<li>Log into Hostinger Control Panel</li>";
echo "<li>Go to File Manager</li>";
echo "<li>Navigate to your website directory (web1)</li>";
echo "<li>Create a new folder called 'PHPMailer'</li>";
echo "<li>Download PHPMailer from GitHub and extract it</li>";
echo "<li>Upload the 'src' folder into the 'PHPMailer' folder you created</li>";
echo "<li>The final structure should be: web1/PHPMailer/src/PHPMailer.php</li>";
echo "</ol>";
echo "<p><strong>Alternative:</strong> You can also use Hostinger's Composer:</p>";
echo "<pre style='background: #f5f5f5; padding: 10px;'>";
echo "1. In Hostinger File Manager, create 'composer.json' with:\n";
echo json_encode(['require' => ['phpmailer/phpmailer' => '^6.8']], JSON_PRETTY_PRINT);
echo "\n2. Then run: composer install";
echo "</pre>";

echo "<h2>Testing Email Configuration</h2>";

// Check .env file
if (file_exists(__DIR__ . '/.env')) {
    echo "<p style='color: green;'>✓ .env file exists</p>";
    
    // Read and display SMTP settings
    $env_content = file_get_contents(__DIR__ . '/.env');
    echo "<pre style='background: #f5f5f5; padding: 10px;'>";
    echo "Current SMTP Configuration:\n";
    echo "===========================\n";
    
    $lines = explode("\n", $env_content);
    foreach ($lines as $line) {
        if (strpos($line, 'SMTP_') === 0 || strpos($line, 'FROM_') === 0) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
    
    // Check for Hostinger recommended settings
    if (strpos($env_content, 'SMTP_PORT=587') !== false && strpos($env_content, 'SMTP_SECURE=tls') !== false) {
        echo "<p style='color: green;'>✓ Using Hostinger's recommended settings (Port 587 with TLS)</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Not using Hostinger's recommended settings. Should be Port 587 with TLS.</p>";
    }
} else {
    echo "<p style='color: red;'>✗ .env file not found. Copy .env.example to .env and configure your SMTP settings.</p>";
}

echo "<h2>Quick Test</h2>";
echo "<p>After installing PHPMailer, you can test the email system:</p>";
echo "<a href='test-email-system.php' target='_blank'>Run Email System Test</a>";

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Install PHPMailer using one of the methods above</li>";
echo "<li>Configure your .env file with Hostinger SMTP credentials</li>";
echo "<li>Test the contact form at contact.html</li>";
echo "<li>Check logs directory for any errors</li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>Note:</strong> This script is for diagnostic purposes only. Delete it after installation is complete.</p>";
?>
