﻿<?php 
// Load .env file if it exists
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            // Only set if not already set (e.g. by Docker)
            if (getenv($name) === false) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
            }
        }
    }
}

require_once __DIR__ . '/Logger.php';

// Set error reporting based on environment
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Production: Log errors to file, do not display
    error_reporting(E_ALL);
    ini_set('display_errors', 0);

    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        $logger = new Logger();
        $logger->error("Error [$errno]: $errstr in $errfile on line $errline");
    });

    set_exception_handler(function ($e) {
        $logger = new Logger();
        $logger->error("Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    });
}

// DB credentials.
define('DB_HOST','fdb15.eohost.com');
define('DB_USER','2520448_armentum');
define('DB_PASS','963852asd');
define('DB_NAME','2520448_armentum');
define('DB_TYPE', 'mysql'); // Options: mysql, mariadb, pgsql, sqlite

require_once __DIR__ . '/Database.php';

// Establish database connection.
$database = new Database();
$dbh = $database->getConnection();
$error = false;
$msg = false;

// Middleware functions
function checkAuth() {
    if (strlen($_SESSION['alogin']) == 0) {
        header('location:index.php');
        exit();
    }
    if (isset($_SESSION['force_pwd_change']) && $_SESSION['force_pwd_change']) {
        if (basename($_SERVER['PHP_SELF']) !== 'change-password.php') {
            header('location:change-password.php');
            exit();
        }
    }
}

function checkAdmin() {
    checkAuth();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('location:profile.php');
        exit();
    }
}