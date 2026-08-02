<?php
// =============================================
//  AUTH / SESSION HELPERS
// =============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';

function current_user() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        flash('Please sign in to continue.', 'error');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        flash('You do not have permission to access that page.', 'error');
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
}

// Default fine per day for late returns
function daily_fine() {
    return 2.00;
}
