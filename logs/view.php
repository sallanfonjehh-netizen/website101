<?php
/**
 * Private Hackers - Log Viewer
 * Securely view error and submission logs
 * 
 * Access: /logs/view.php
 * 
 * SECURITY: Only accessible locally or with valid token
 */

// Define log directory
define('LOG_DIR', dirname(__FILE__));

// Security: Check if accessing locally or with valid token
$is_local = in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', 'localhost'));
$token_valid = isset($_GET['token']) && $_GET['token'] === md5('privatehackers_' . date('Y-m-d'));

if (!$is_local && !$token_valid) {
    http_response_code(403);
    die('Access Denied. Local access or valid token required.');
}

// Get log type from query parameter
$log_type = isset($_GET['type']) ? $_GET['type'] : 'errors';
$log_file = LOG_DIR . '/' . $log_type . '.log';

// Available log files
$available_logs = array(
    'errors' => 'Error Log',
    'email_delivery' => 'Email Delivery Log',
    'submissions' => 'Form Submissions Log',
    'debug' => 'Debug Log'
);

// Sanitize log type
if (!in_array($log_type, array_keys($available_logs))) {
    $log_type = 'errors';
    $log_file = LOG_DIR . '/errors.log';
}

// Get log content
$log_content = '';
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
} else {
    $log_content = "No logs recorded yet.";
}

// Reverse order to show latest entries first
$lines = array_reverse(explode("\n", trim($log_content)));
$log_content = implode("\n", $lines);

// Get file size and last modified time
$file_size = file_exists($log_file) ? filesize($log_file) : 0;
$last_modified = file_exists($log_file) ? date('Y-m-d H:i:s', filemtime($log_file)) : 'N/A';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Private Hackers - Log Viewer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            background: #0a0a0a;
            color: #00ff41;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #1a1a1a;
            border: 2px solid #00ff41;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0, 255, 65, 0.3);
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            text-shadow: 0 0 10px rgba(0, 255, 65, 0.8);
        }
        
        .controls {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .log-selector {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        button, a {
            background: #00ff41;
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        button:hover, a:hover {
            background: #00d4ff;
            box-shadow: 0 0 10px rgba(0, 212, 255, 0.5);
        }
        
        button.active {
            background: #ff6600;
            box-shadow: 0 0 10px rgba(255, 102, 0, 0.5);
        }
        
        .info {
            background: #1a1a1a;
            border: 1px solid #00d4ff;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            color: #00d4ff;
        }
        
        .info-item {
            display: inline-block;
            margin-right: 30px;
        }
        
        .log-viewer {
            background: #000;
            border: 1px solid #00ff41;
            padding: 15px;
            border-radius: 4px;
            max-height: 600px;
            overflow-y: auto;
            line-height: 1.5;
        }
        
        .log-entry {
            padding: 5px 0;
            border-bottom: 1px solid #2a2a2a;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .log-entry:last-child {
            border-bottom: none;
        }
        
        .error {
            color: #ff073a;
        }
        
        .success {
            color: #00ff41;
        }
        
        .warning {
            color: #ffff00;
        }
        
        .info-text {
            color: #00d4ff;
        }
        
        .timestamp {
            color: #888;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-box {
            background: #2a2a2a;
            border-left: 4px solid #00ff41;
            padding: 15px;
            border-radius: 4px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 20px;
            color: #00ff41;
            font-weight: bold;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #888;
            font-size: 12px;
        }
        
        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }
            
            h1 {
                font-size: 18px;
            }
            
            .controls {
                flex-direction: column;
            }
            
            button, a {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Private Hackers - Log Viewer</h1>
        
        <div class="stats">
            <div class="stat-box">
                <div class="stat-label">Current Log</div>
                <div class="stat-value"><?php echo $available_logs[$log_type]; ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">File Size</div>
                <div class="stat-value"><?php echo formatBytes($file_size); ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Last Modified</div>
                <div class="stat-value" style="font-size: 14px;"><?php echo $last_modified; ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Log Entries</div>
                <div class="stat-value"><?php echo substr_count($log_content, "\n") + 1; ?></div>
            </div>
        </div>
        
        <div class="controls">
            <div class="log-selector">
                <?php foreach ($available_logs as $type => $label): ?>
                    <a href="?type=<?php echo $type; ?><?php echo isset($_GET['token']) ? '&token=' . $_GET['token'] : ''; ?>" 
                       class="<?php echo $type === $log_type ? 'active' : ''; ?>" 
                       style="<?php echo $type === $log_type ? 'pointer-events: none;' : ''; ?>">
                        <?php echo $label; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="info">
            <div class="info-item">
                <strong>🔐 Access:</strong> Local or with valid token
            </div>
            <div class="info-item">
                <strong>📊 Status:</strong> <span class="success">● Active</span>
            </div>
            <div class="info-item">
                <strong>🔄 Auto-refresh:</strong> Off (Manual refresh required)
            </div>
        </div>
        
        <div class="log-viewer">
            <?php
            if (empty($log_content) || $log_content === 'No logs recorded yet.') {
                echo '<div class="log-entry info-text">No logs recorded yet.</div>';
            } else {
                $entries = explode("\n", trim($log_content));
                foreach ($entries as $entry) {
                    if (empty($entry)) continue;
                    
                    $class = 'info-text';
                    if (strpos($entry, 'ERROR') !== false || strpos($entry, 'FAILED') !== false) {
                        $class = 'error';
                    } elseif (strpos($entry, 'SUCCESS') !== false) {
                        $class = 'success';
                    } elseif (strpos($entry, 'WARNING') !== false) {
                        $class = 'warning';
                    }
                    
                    echo '<div class="log-entry ' . $class . '">' . htmlspecialchars($entry) . '</div>';
                }
            }
            ?>
        </div>
        
        <div class="controls" style="margin-top: 20px;">
            <button onclick="location.reload()">🔄 Refresh</button>
            <a href="javascript:void(0);" onclick="downloadLogs()">📥 Download Logs</a>
        </div>
        
        <div class="footer">
            <p>Private Hackers Form System | Log Viewer v1.0</p>
            <p style="margin-top: 10px; color: #666;">Logs are automatically created and maintained by the form submission system</p>
        </div>
    </div>
    
    <script>
        function downloadLogs() {
            const type = new URLSearchParams(window.location.search).get('type') || 'errors';
            const element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(document.querySelector('.log-viewer').innerText));
            element.setAttribute('download', 'privatehackers_' + type + '_' + new Date().toISOString().split('T')[0] + '.log');
            element.style.display = 'none';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
        }
    </script>
</body>
</html>

<?php

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

?>
