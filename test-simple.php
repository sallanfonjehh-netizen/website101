<?php
// Simple test script
header('Content-Type: application/json');

// Test if we can write to logs directory
$logs_dir = __DIR__ . '/logs';
if (!is_dir($logs_dir)) {
    @mkdir($logs_dir, 0755, true);
}

$test_file = $logs_dir . '/test.txt';
$write_result = @file_put_contents($test_file, 'Test at ' . date('Y-m-d H:i:s'));

echo json_encode([
    'success' => true,
    'logs_dir_exists' => is_dir($logs_dir),
    'logs_dir_writable' => is_writable($logs_dir),
    'test_write_success' => $write_result !== false,
    'test_write_result' => $write_result,
    'php_version' => phpversion(),
    'error_reporting' => error_reporting(),
    'display_errors' => ini_get('display_errors')
], JSON_PRETTY_PRINT);

@unlink($test_file);
?>
