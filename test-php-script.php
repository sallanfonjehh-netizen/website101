<?php
/**
 * Test script to verify PHP is working and check for errors
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Test basic PHP functionality
$php_info = [
    'php_version' => phpversion(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
    'script_path' => __FILE__,
    'working_directory' => getcwd(),
    'file_exists_config' => file_exists(__DIR__ . '/config.php'),
    'file_exists_env' => file_exists(__DIR__ . '/.env'),
    'file_exists_submit_form' => file_exists(__DIR__ . '/submit-form.php'),
    'is_writable_logs' => is_writable(__DIR__ . '/logs'),
    'error_log' => ini_get('error_log'),
    'display_errors' => ini_get('display_errors'),
    'error_reporting' => error_reporting(),
];

// Test if we can write to logs directory
$logs_dir = __DIR__ . '/logs';
if (!is_dir($logs_dir)) {
    @mkdir($logs_dir, 0755, true);
}

// Test file permissions
$test_file = $logs_dir . '/test_permissions.txt';
$write_test = @file_put_contents($test_file, 'Test write at ' . date('Y-m-d H:i:s'));
@unlink($test_file);

$php_info['can_write_logs'] = (bool)$write_test;

// Test config.php loading
$config_loaded = false;
$config_error = '';
if (file_exists(__DIR__ . '/config.php')) {
    try {
        require_once __DIR__ . '/config.php';
        $config_loaded = true;
        $php_info['config_class_exists'] = class_exists('Config');
        
        if (class_exists('Config')) {
            try {
                $php_info['config_values'] = [
                    'smtp_host' => Config::get('SMTP_HOST', 'Not set'),
                    'from_email' => Config::get('FROM_EMAIL', 'Not set'),
                    'admin_email' => Config::get('ADMIN_EMAIL', 'Not set'),
                ];
            } catch (Exception $e) {
                $php_info['config_get_error'] = $e->getMessage();
            }
        }
    } catch (Exception $e) {
        $config_error = $e->getMessage();
        $php_info['config_load_error'] = $config_error;
    }
}

// Test PHPMailer loading
$phpmailer_paths = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/PHPMailer/src/PHPMailer.php',
    __DIR__ . '/phpmailer/src/PHPMailer.php',
    __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php'
];

$phpmailer_found = false;
$phpmailer_path = '';
foreach ($phpmailer_paths as $path) {
    if (file_exists($path)) {
        $phpmailer_found = true;
        $phpmailer_path = $path;
        break;
    }
}

$php_info['phpmailer_found'] = $phpmailer_found;
$php_info['phpmailer_path'] = $phpmailer_path;

// Test JSON encoding
$test_data = ['test' => 'data', 'timestamp' => time()];
$json_test = json_encode($test_data);
$php_info['json_working'] = $json_test !== false;

// Test error handling
$error_test = '';
set_error_handler(function($errno, $errstr) use (&$error_test) {
    $error_test = "Error $errno: $errstr";
    return true;
});

// Trigger a test error
@$undefined_variable;

restore_error_handler();
$php_info['error_handling_working'] = !empty($error_test);

// Return results
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'PHP script test completed',
    'php_info' => $php_info,
    'system_status' => [
        'php_working' => true,
        'config_loaded' => $config_loaded,
        'phpmailer_available' => $phpmailer_found,
        'can_write_logs' => (bool)$write_test,
        'json_working' => $json_test !== false,
        'overall_status' => $config_loaded && $phpmailer_found && ($write_test !== false) ? 'READY' : 'PARTIAL'
    ],
    'recommendations' => array_filter([
        !$config_loaded ? 'config.php not loaded or has errors' : null,
        !$phpmailer_found ? 'PHPMailer not found. Install via: composer require phpmailer/phpmailer' : null,
        $write_test === false ? 'Cannot write to logs directory. Check permissions.' : null,
    ]),
    'timestamp' => date('Y-m-d H:i:s')
], JSON_PRETTY_PRINT);
?>
