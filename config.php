<?php
/**
 * Configuration Loader for Private Hackers Email System
 * 
 * Loads environment variables from .env file securely
 * Provides centralized configuration management
 */

class Config {
    private static $config = [];
    
    /**
     * Load configuration from .env file
     */
    public static function load() {
        $envFile = __DIR__ . '/.env';
        
        if (!file_exists($envFile)) {
            throw new Exception('.env file not found. Please copy .env.example to .env and configure your settings.');
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
                    $value = $matches[1];
                }
                
                self::$config[$key] = $value;
            }
        }
        
        // Validate required configuration
        self::validate();
    }
    
    /**
     * Get configuration value
     */
    public static function get($key, $default = null) {
        return self::$config[$key] ?? $default;
    }
    
    /**
     * Validate required configuration
     */
    private static function validate() {
        $required = [
            'SMTP_HOST',
            'SMTP_PORT',
            'SMTP_USERNAME',
            'SMTP_PASSWORD',
            'FROM_EMAIL',
            'FROM_NAME',
            'ADMIN_EMAIL'
        ];
        
        $missing = [];
        foreach ($required as $key) {
            if (!isset(self::$config[$key]) || empty(self::$config[$key])) {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception('Missing required configuration in .env file: ' . implode(', ', $missing));
        }
    }
    
    /**
     * Get SMTP configuration array
     */
    public static function getSmtpConfig() {
        return [
            'host' => self::get('SMTP_HOST'),
            'port' => (int) self::get('SMTP_PORT'),
            'secure' => self::get('SMTP_SECURE', 'ssl'),
            'username' => self::get('SMTP_USERNAME'),
            'password' => self::get('SMTP_PASSWORD')
        ];
    }
    
    /**
     * Get email configuration
     */
    public static function getEmailConfig() {
        return [
            'from_email' => self::get('FROM_EMAIL'),
            'from_name' => self::get('FROM_NAME'),
            'admin_email' => self::get('ADMIN_EMAIL')
        ];
    }
    
    /**
     * Get company information
     */
    public static function getCompanyInfo() {
        return [
            'name' => self::get('COMPANY_NAME', 'Private Hackers'),
            'phone' => self::get('COMPANY_PHONE', '+1 (782) 377-6721'),
            'address' => self::get('COMPANY_ADDRESS', '58 Solomons Court, Glencar, Letterkenny, Co. Donegal'),
            'website' => self::get('COMPANY_WEBSITE', 'https://privatehakers.com')
        ];
    }
    
    /**
     * Check if debug mode is enabled
     */
    public static function isDebug() {
        return filter_var(self::get('DEBUG_MODE', 'false'), FILTER_VALIDATE_BOOLEAN);
    }
}

// Load configuration automatically
try {
    Config::load();
} catch (Exception $e) {
    // Log error but don't expose details in production
    error_log('Configuration error: ' . $e->getMessage());
    
    // In debug mode, show the error
    if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost') {
        die('Configuration Error: ' . $e->getMessage());
    }
}
