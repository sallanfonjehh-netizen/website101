<?php
/**
 * Private Hackers - Production-Ready Form Submission Handler
 * 
 * Features:
 * - Secure PHPMailer SMTP integration with Hostinger
 * - Comprehensive error handling and logging
 * - JSON API response format
 * - Input validation and sanitization
 * - Configurable via .env file
 * - No hardcoded credentials
 */

// ============================================================================
// CONFIGURATION & BOOTSTRAP
// ============================================================================

// Try to load configuration
$configLoaded = false;
$configError = '';

try {
    if (file_exists(__DIR__ . '/config.php')) {
        require_once __DIR__ . '/config.php';
        $configLoaded = true;
    } else {
        $configError = 'config.php not found';
    }
} catch (Exception $e) {
    $configError = $e->getMessage();
}

// Try to load PHPMailer using Hostinger's recommended method
$phpmailerLoaded = false;
$phpmailerError = '';

// Paths to check for PHPMailer (in order of preference)
$phpmailer_paths = [
    // Composer installation
    __DIR__ . '/vendor/autoload.php',
    // Direct PHPMailer folder (Hostinger's recommended method)
    __DIR__ . '/PHPMailer/src/PHPMailer.php',
    __DIR__ . '/phpmailer/src/PHPMailer.php',
    __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php'
];

foreach ($phpmailer_paths as $path) {
    if (file_exists($path)) {
        try {
            if (strpos($path, 'autoload.php') !== false) {
                // Composer autoloader
                require_once $path;
                $phpmailerLoaded = true;
                break;
            } else {
                // Direct PHPMailer files (Hostinger's method)
                require_once dirname($path) . '/Exception.php';
                require_once $path; // PHPMailer.php
                require_once dirname($path) . '/SMTP.php';
                $phpmailerLoaded = true;
                break;
            }
        } catch (Exception $e) {
            $phpmailerError = $e->getMessage();
        }
    }
}

// If config or PHPMailer not loaded, we'll still accept the form but use local storage
$systemReady = $configLoaded && $phpmailerLoaded;

// ============================================================================
// LOGGING SYSTEM
// ============================================================================

define('LOG_DIR', __DIR__ . '/logs');
define('ERROR_LOG_FILE', LOG_DIR . '/errors.log');
define('EMAIL_LOG_FILE', LOG_DIR . '/email_delivery.log');
define('SUBMISSION_LOG_FILE', LOG_DIR . '/submissions.log');

/**
 * Ensure log directory exists with proper permissions
 */
function ensure_log_directory() {
    if (!is_dir(LOG_DIR)) {
        @mkdir(LOG_DIR, 0755, true);
        // Create .htaccess to prevent public access
        $htaccess_content = "# Prevent direct access to logs\n<Files *>\n    Order allow,deny\n    Deny from all\n</Files>";
        @file_put_contents(LOG_DIR . '/.htaccess', $htaccess_content);
    }
}

/**
 * Log error messages
 */
function log_error($message, $severity = 'ERROR', $context = []) {
    ensure_log_directory();
    
    $timestamp = date('Y-m-d H:i:s');
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $log_entry = sprintf(
        "[%s] [%s] [IP: %s] %s",
        $timestamp,
        $severity,
        $ip_address,
        $message
    );
    
    if (!empty($context)) {
        $log_entry .= " | Context: " . json_encode($context, JSON_UNESCAPED_SLASHES);
    }
    
    $log_entry .= "\n";
    
    @file_put_contents(ERROR_LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Log email delivery attempts
 */
function log_email_delivery($ticket_id, $recipient, $subject, $status, $error = '') {
    ensure_log_directory();
    
    $timestamp = date('Y-m-d H:i:s');
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $log_entry = sprintf(
        "[%s] Ticket: %s | To: %s | Status: %s | Subject: %s | IP: %s",
        $timestamp,
        $ticket_id,
        $recipient,
        $status,
        substr($subject, 0, 50),
        $ip_address
    );
    
    if (!empty($error)) {
        $log_entry .= " | Error: " . substr($error, 0, 200);
    }
    
    $log_entry .= "\n";
    
    @file_put_contents(EMAIL_LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Log form submissions
 */
function log_form_submission($ticket_id, $name, $email, $service, $priority) {
    ensure_log_directory();
    
    $timestamp = date('Y-m-d H:i:s');
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $log_entry = sprintf(
        "[%s] Ticket: %s | Name: %s | Email: %s | Service: %s | Priority: %s | IP: %s\n",
        $timestamp,
        $ticket_id,
        $name,
        $email,
        $service,
        $priority,
        $ip_address
    );
    
    @file_put_contents(SUBMISSION_LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);
}

// Set up error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    log_error(
        "PHP Error: " . $errstr . " in " . basename($errfile) . " on line " . $errline,
        'PHP_ERROR',
        ['errno' => $errno, 'file' => $errfile, 'line' => $errline]
    );
    return false;
});

// Set up exception handler
set_exception_handler(function($exception) {
    log_error(
        "Exception: " . $exception->getMessage(),
        'EXCEPTION',
        [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]
    );
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error occurred']);
    exit();
});

// ============================================================================
// REQUEST HANDLING
// ============================================================================

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    log_error('Invalid request method', 'WARNING', ['method' => $_SERVER['REQUEST_METHOD']]);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    log_error('Invalid JSON input', 'VALIDATION_ERROR', ['json_error' => json_last_error_msg()]);
    echo json_encode(['success' => false, 'error' => 'Invalid request data']);
    exit();
}

// ============================================================================
// VALIDATION
// ============================================================================

// Validate required fields
$required_fields = ['name', 'email', 'phone', 'service', 'priority', 'description'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        log_error("Validation failed: Missing required field", 'VALIDATION_ERROR', ['field' => $field]);
        echo json_encode(['success' => false, 'error' => "Field '$field' is required"]);
        exit();
    }
}

// Extract and sanitize data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function sanitize_email($email) {
    $email = trim($email);
    $email = strtolower($email);
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}

$name = sanitize_input($input['name']);
$email = sanitize_email($input['email']);
$phone = sanitize_input($input['phone']);
$service = sanitize_input($input['service']);
$priority = sanitize_input($input['priority']);
$description = sanitize_input($input['description']);
$features = isset($input['features']) ? array_map('sanitize_input', (array)$input['features']) : [];

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    log_error("Validation failed: Invalid email format", 'VALIDATION_ERROR', ['email' => $email]);
    echo json_encode(['success' => false, 'error' => 'Invalid email address']);
    exit();
}

// Validate phone
if (!preg_match('/^[0-9\s\-\+\(\)\*\#\.\\/]{7,}$/', $phone)) {
    http_response_code(400);
    log_error("Validation failed: Invalid phone format", 'VALIDATION_ERROR', ['phone' => substr($phone, 0, 5) . '***']);
    echo json_encode(['success' => false, 'error' => 'Invalid phone number']);
    exit();
}

// ============================================================================
// PROCESSING
// ============================================================================

// Generate unique ticket ID
function generate_ticket_id() {
    $year = date('Y');
    $random = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    return "PRIVATE-" . $year . "-" . $random;
}

$ticketId = generate_ticket_id();

// Get configuration if available
$smtp_config = [];
$email_config = [];
$company_info = [];

if ($configLoaded) {
    try {
        $smtp_config = Config::getSmtpConfig();
        $email_config = Config::getEmailConfig();
        $company_info = Config::getCompanyInfo();
    } catch (Exception $e) {
        // Config loaded but error getting values
        log_error('Config error: ' . $e->getMessage(), 'CONFIG_ERROR');
    }
} else {
    // Use defaults if config not loaded
    $company_info = [
        'name' => 'Private Hackers',
        'phone' => '+1 (782) 377-6721',
        'address' => '58 Solomons Court, Glencar, Letterkenny, Co. Donegal',
        'website' => 'https://privatehakers.com'
    ];
}

// Prepare email content
$features_html = '';
if (!empty($features)) {
    $features_html = '<ul style="margin: 10px 0; padding-left: 20px;">';
    foreach ($features as $feature) {
        $features_html .= '<li>' . htmlspecialchars($feature) . '</li>';
    }
    $features_html .= '</ul>';
}

// Client email body
$client_email_body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1a1a1a; color: #0ff; padding: 20px; text-align: center; border-radius: 5px; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .ticket-id { background: #0ff; color: #000; padding: 10px; text-align: center; font-weight: bold; font-size: 18px; border-radius: 5px; margin: 15px 0; font-family: monospace; }
        .field { margin: 15px 0; }
        .label { font-weight: bold; color: #0066cc; }
        .footer { text-align: center; font-size: 12px; color: #666; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$company_info['name']}</h1>
            <p style="margin: 5px 0;">Confidentiality Guaranteed | Expert Support</p>
        </div>
        
        <div class="content">
            <h2>Request Confirmation</h2>
            <p>Thank you for submitting your request, <strong>$name</strong>!</p>
            
            <div class="ticket-id">Ticket ID: $ticketId</div>
            
            <p>We've received your information and will review it shortly. Our team will contact you within 24 hours.</p>
            
            <h3>Your Request Details:</h3>
            
            <div class="field">
                <div class="label">Service Requested:</div>
                <div>$service</div>
            </div>
            
            <div class="field">
                <div class="label">Priority Level:</div>
                <div>$priority</div>
            </div>
            
            <div class="field">
                <div class="label">Description:</div>
                <div>$description</div>
            </div>
            
            <div class="field">
                <div class="label">Selected Features:</div>
                $features_html
            </div>
            
            <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
            
            <h3>Contact Information on File:</h3>
            
            <div class="field">
                <div class="label">Name:</div>
                <div>$name</div>
            </div>
            
            <div class="field">
                <div class="label">Email:</div>
                <div>$email</div>
            </div>
            
            <div class="field">
                <div class="label">Phone:</div>
                <div>$phone</div>
            </div>
        </div>
        
        <div class="footer">
            <p>{$company_info['name']} | {$company_info['phone']} | {$company_info['address']}</p>
            <p><a href="{$company_info['website']}">Visit our website</a></p>
            <p style="color: #999;">This is an automated confirmation email. Do not reply directly to this message.</p>
        </div>
    </div>
</body>
</html>
HTML;

// Admin email body
$admin_email_body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1a1a1a; color: #0ff; padding: 20px; text-align: center; border-radius: 5px; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .ticket-id { background: #ff6600; color: #fff; padding: 10px; text-align: center; font-weight: bold; font-size: 18px; border-radius: 5px; margin: 15px 0; font-family: monospace; }
        .field { margin: 15px 0; }
        .label { font-weight: bold; color: #0066cc; }
        .footer { text-align: center; font-size: 12px; color: #666; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$company_info['name']} - New Service Request</h1>
            <p style="margin: 5px 0;">Ticket Management System</p>
        </div>
        
        <div class="content">
            <h2>⚠️ New Request Received</h2>
            
            <div class="ticket-id">Ticket ID: $ticketId</div>
            
            <h3>Client Information:</h3>
            
            <div class="field">
                <div class="label">Name:</div>
                <div>$name</div>
            </div>
            
            <div class="field">
                <div class="label">Email:</div>
                <div><a href="mailto:$email">$email</a></div>
            </div>
            
            <div class="field">
                <div class="label">Phone:</div>
                <div><a href="tel:$phone">$phone</a></div>
            </div>
            
            <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
            
            <h3>Request Details:</h3>
            
            <div class="field">
                <div class="label">Service:</div>
                <div>$service</div>
            </div>
            
            <div class="field">
                <div class="label">Priority:</div>
                <div>$priority</div>
            </div>
            
            <div class="field">
                <div class="label">Description:</div>
                <div>$description</div>
            </div>
            
            <div class="field">
                <div class="label">Selected Features:</div>
                $features_html
            </div>
            
            <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
            
            <p><strong>Timestamp:</strong> {date('Y-m-d H:i:s')}</p>
            <p><strong>Status:</strong> Open</p>
        </div>
        
        <div class="footer">
            <p>This is an automated ticket notification. Log in to your admin panel to manage requests.</p>
        </div>
    </div>
</body>
</html>
HTML;

// Email subjects
$client_subject = "Request Confirmation - Ticket ID: " . $ticketId;
$admin_subject = "[{$company_info['name']}] New Service Request - Ticket: " . $ticketId;

// ============================================================================
// EMAIL SENDING FUNCTION
// ============================================================================

/**
 * Send email via PHPMailer using Hostinger SMTP (Hostinger's recommended method)
 */
function send_email_via_smtp($to, $subject, $body, $smtp_config, $email_config, $reply_to = null, $ticket_id = '') {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings (Hostinger's recommended configuration)
        $mail->SMTPDebug = 0; // Disable debug output for production
        $mail->isSMTP();
        $mail->Host       = $smtp_config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_config['username'];
        $mail->Password   = $smtp_config['password'];
        $mail->SMTPSecure = $smtp_config['secure']; // 'ssl' for port 465, 'tls' for port 587
        $mail->Port       = $smtp_config['port'];   // 465 for SSL, 587 for TLS
        
        // Character set
        $mail->CharSet = 'UTF-8';
        
        // Sender (must match SMTP username for Hostinger)
        $mail->setFrom($email_config['from_email'], $email_config['from_name']);
        
        // Reply-To (use client's email if provided)
        if ($reply_to) {
            $mail->addReplyTo($reply_to, $email_config['from_name']);
        }
        
        // Recipient
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);
        
        // Send the email
        if ($mail->send()) {
            log_email_delivery($ticket_id, $to, $subject, 'SUCCESS');
            return true;
        } else {
            $error = $mail->ErrorInfo;
            log_email_delivery($ticket_id, $to, $subject, 'FAILED', $error);
            
            // Log detailed error for debugging
            log_error("Email sending failed: $error", 'EMAIL_ERROR', [
                'ticket_id' => $ticket_id,
                'recipient' => $to,
                'smtp_host' => $smtp_config['host'],
                'smtp_port' => $smtp_config['port']
            ]);
            
            return false;
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        log_email_delivery($ticket_id, $to, $subject, 'EXCEPTION', $error);
        
        // Log exception details
        log_error("Email exception: $error", 'EMAIL_EXCEPTION', [
            'ticket_id' => $ticket_id,
            'recipient' => $to,
            'exception' => $error
        ]);
        
        return false;
    }
}

// ============================================================================
// SEND EMAILS
// ============================================================================

// Send confirmation email to client if system is ready
$client_sent = false;
$admin_sent = false;
$email_errors = [];

if ($systemReady && !empty($smtp_config) && !empty($email_config)) {
    try {
        $client_sent = send_email_via_smtp(
            $email,
            $client_subject,
            $client_email_body,
            $smtp_config,
            $email_config,
            $email,
            $ticketId
        );
    } catch (Exception $e) {
        $email_errors[] = 'Client email failed: ' . $e->getMessage();
        log_error('Client email failed: ' . $e->getMessage(), 'EMAIL_ERROR', ['ticket_id' => $ticketId, 'recipient' => $email]);
    }

    try {
        $admin_sent = send_email_via_smtp(
            $email_config['admin_email'],
            $admin_subject,
            $admin_email_body,
            $smtp_config,
            $email_config,
            $email,
            $ticketId
        );
    } catch (Exception $e) {
        $email_errors[] = 'Admin email failed: ' . $e->getMessage();
        log_error('Admin email failed: ' . $e->getMessage(), 'EMAIL_ERROR', ['ticket_id' => $ticketId, 'recipient' => $email_config['admin_email']]);
    }
} else {
    // System not ready for email sending
    $email_errors[] = 'Email system not configured. ' . 
                     ($configLoaded ? '' : 'Config not loaded. ') . 
                     ($phpmailerLoaded ? '' : 'PHPMailer not installed.');
    log_error('Email system not ready', 'EMAIL_SYSTEM', [
        'config_loaded' => $configLoaded,
        'phpmailer_loaded' => $phpmailerLoaded,
        'config_error' => $configError,
        'phpmailer_error' => $phpmailerError
    ]);
}

// Log the submission
log_form_submission($ticketId, $name, $email, $service, $priority);

// ============================================================================
// RESPONSE
// ============================================================================

// Always return success for form submission
$overall_success = true; // Form submission is always successful
$emails_sent = $client_sent || $admin_sent;
$system_ready = $systemReady;

http_response_code(200);
echo json_encode([
    'success' => true,
    'ticketId' => $ticketId,
    'message' => $system_ready ? 
        ($emails_sent ? 
            'Request submitted successfully. Check your email for confirmation.' : 
            'Request submitted successfully but email confirmation failed. Please save your Ticket ID: ' . $ticketId) :
        'Request submitted successfully. Email system not configured - please save your Ticket ID: ' . $ticketId,
    'clientEmailSent' => $client_sent,
    'adminEmailSent' => $admin_sent,
    'systemReady' => $system_ready,
    'emailErrors' => !empty($email_errors) ? $email_errors : null,
    'localStorage' => !$system_ready || !$emails_sent,
    'configStatus' => $configLoaded ? 'loaded' : 'missing',
    'phpmailerStatus' => $phpmailerLoaded ? 'loaded' : 'missing'
]);

exit();
