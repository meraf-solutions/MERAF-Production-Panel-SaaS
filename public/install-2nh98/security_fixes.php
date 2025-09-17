<?php
/**
 * CRITICAL SECURITY FIXES FOR INSTALLER
 * Apply these fixes immediately before deployment
 */

// 1. SECURE INPUT SANITIZATION
function sanitize_input($input) {
    if (is_string($input)) {
        $input = strip_tags($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        $input = str_replace("\0", '', $input);
        return trim($input);
    }
    return $input;
}

// 2. VALIDATE DOMAIN NAME (Fix for C1)
function validate_domain_name($domain) {
    // Remove protocol if present
    $domain = preg_replace('#^https?://#', '', $domain);
    
    // Basic domain validation
    if (!filter_var('http://' . $domain, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    // Check for dangerous characters
    if (preg_match('/[<>"\'\\\\\s;]/', $domain)) {
        return false;
    }
    
    return $domain;
}

// 3. SECURE RANDOM GENERATION (Fix for C3)
function generateSecureRandomChars($length = 5) {
    try {
        $randomBytes = random_bytes($length);
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $result = '';
        
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[ord($randomBytes[$i]) % strlen($chars)];
        }
        
        return $result;
    } catch (Exception $e) {
        // Fallback to mt_rand if random_bytes fails
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $result;
    }
}

// 4. SECURE DATABASE CONNECTION
function createSecureDatabaseConnection($host, $username, $password, $database) {
    // Validate inputs
    $host = sanitize_input($host);
    $username = sanitize_input($username);
    $database = sanitize_input($database);
    
    // Validate host format
    if (!filter_var($host, FILTER_VALIDATE_IP) && !filter_var('http://' . $host, FILTER_VALIDATE_URL)) {
        throw new Exception('Invalid database host format');
    }
    
    // Create connection with error handling
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        $conn = new mysqli($host, $username, $password, $database);
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (mysqli_sql_exception $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}

// 5. SECURE SQL EXECUTION
function executeSQLFile($conn, $sqlFile, $domainName) {
    // Validate domain name first
    $safeDomain = validate_domain_name($domainName);
    if (!$safeDomain) {
        throw new Exception('Invalid domain name provided');
    }
    
    if (!file_exists($sqlFile)) {
        throw new Exception('SQL file not found');
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Use prepared statement approach for domain replacement
    $sql = str_replace('{{domain_name}}', '?', $sql);
    
    // Split and execute queries safely
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($queries as $query) {
        if (empty($query)) continue;
        
        if (strpos($query, '?') !== false) {
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception('SQL prepare failed: ' . $conn->error);
            }
            $stmt->bind_param('s', $safeDomain);
            $stmt->execute();
            $stmt->close();
        } else {
            $conn->query($query);
        }
    }
}

// 6. SECURE FILE OPERATIONS
function writeConfigFileSecurely($filePath, $content) {
    // Validate file path
    $realPath = realpath(dirname($filePath));
    if ($realPath === false) {
        throw new Exception('Invalid file path');
    }
    
    // Write to temporary file first
    $tempFile = $filePath . '.tmp';
    
    if (file_put_contents($tempFile, $content, LOCK_EX) === false) {
        throw new Exception('Failed to write temporary configuration file');
    }
    
    // Atomic move
    if (!rename($tempFile, $filePath)) {
        unlink($tempFile);
        throw new Exception('Failed to move configuration file');
    }
    
    return true;
}

// 7. INPUT VALIDATION FOR INSTALLER
function validateInstallerInput($data) {
    $errors = [];
    
    // Database validation
    if (empty($data['host']) || !validate_domain_name($data['host'])) {
        $errors[] = 'Invalid database host';
    }
    
    if (empty($data['username']) || strlen($data['username']) > 64) {
        $errors[] = 'Invalid database username';
    }
    
    if (empty($data['database']) || !preg_match('/^[a-zA-Z0-9_]+$/', $data['database'])) {
        $errors[] = 'Invalid database name';
    }
    
    // Email validation
    if (!in_array($data['protocol'], ['smtp', 'sendmail', 'mail'])) {
        $errors[] = 'Invalid email protocol';
    }
    
    if ($data['protocol'] === 'smtp') {
        if (empty($data['smtpHostname']) || !validate_domain_name($data['smtpHostname'])) {
            $errors[] = 'Invalid SMTP hostname';
        }
        
        if (!filter_var($data['smtpUsername'], FILTER_VALIDATE_EMAIL) && !empty($data['smtpUsername'])) {
            $errors[] = 'Invalid SMTP username format';
        }
        
        $port = intval($data['smtpPort']);
        if ($port < 1 || $port > 65535) {
            $errors[] = 'Invalid SMTP port';
        }
        
        if (!in_array($data['smtpEncryption'], ['tls', 'ssl', ''])) {
            $errors[] = 'Invalid SMTP encryption';
        }
    }
    
    return $errors;
}

// 8. DISABLE DEBUG MODE IN PRODUCTION
function disableDebugMode() {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

?>