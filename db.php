<?php
// db.php
// Central database connection loader.

// Prefer loading credentials from config.php created by setup wizard
// Fall back to legacy constants or redirect to setup if missing.

// Start session on all pages that include this file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration if present
$config_loaded = false;
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
    if (defined('DB_SERVER') && defined('DB_USERNAME') && defined('DB_PASSWORD') && defined('DB_NAME')) {
        $config_loaded = true;
    }
}

// Legacy fallback (for existing deployments without config.php)
if (!$config_loaded) {
    // If legacy constants exist below, use them; otherwise guide user to setup
    if (!defined('DB_SERVER')) {
        define('DB_SERVER', 'localhost');
    }
    if (!defined('DB_USERNAME')) {
        define('DB_USERNAME', '');
    }
    if (!defined('DB_PASSWORD')) {
        define('DB_PASSWORD', '');
    }
    if (!defined('DB_NAME')) {
        define('DB_NAME', '');
    }
}

// If we still don't have required config, redirect to setup wizard
if (empty(DB_USERNAME) || empty(DB_NAME)) {
    if (php_sapi_name() !== 'cli') {
        header('Location: setup.php');
        exit;
    } else {
        die("Database not configured. Please run setup.php to create config.php.");
    }
}

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false || $conn->connect_error){
    die("ERROR: Could not connect. " . $conn->connect_error);
}
?>
