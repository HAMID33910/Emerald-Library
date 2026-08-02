<?php
// =============================================
//  DATABASE CONNECTION + BASE URL
// =============================================

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'library_system';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die('Database connection failed: ' . htmlspecialchars($conn->connect_error) .
        '. Make sure MySQL is running and the database is installed (run install.php).');
}
$conn->set_charset('utf8mb4');

// ---- Compute base URL so sub-folders can use absolute paths ----
$docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
$appRoot = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
$rel = str_replace($docRoot, '', $appRoot);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $rel);
define('APP_ROOT', $appRoot);
define('UPLOAD_DIR', $appRoot . '/uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');
